const express = require('express');
const fs = require('fs');
const path = require('path');
const cors = require('cors');
const session = require('express-session');
const passport = require('passport');
const GoogleStrategy = require('passport-google-oauth20').Strategy;
const FacebookStrategy = require('passport-facebook').Strategy;
const LinkedInStrategy = require('passport-linkedin-oauth2').Strategy;
require('dotenv').config();

const app = express();

// ========== MIDDLEWARE ==========

app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(cors({
    origin: 'http://localhost:3000', // Update with your client URL
    methods: ['POST', 'GET']
}));

app.use(session({
  secret: process.env.SESSION_SECRET || 'default_secret',
  resave: false,
  saveUninitialized: true,
  cookie: { secure: false }
}));

app.use(passport.initialize());
app.use(passport.session());

// ========== FILE PATHS ==========
const USERS_FILE = path.join(__dirname, 'users.json');
const CONTACTS_FILE = path.join(__dirname, 'contacts.json');
const APPOINTMENTS_FILE = path.join(__dirname, 'appointments.json');

// ========== INIT FILES IF MISSING ==========
[USERS_FILE, CONTACTS_FILE, APPOINTMENTS_FILE].forEach(file => {
  if (!fs.existsSync(file)) fs.writeFileSync(file, '[]');
});

// ========== SOCIAL LOGIN HANDLER ==========
async function handleSocialUser(profile, provider) {
  const users = JSON.parse(await fs.promises.readFile(USERS_FILE, 'utf8'));
  let existingUser = users.find(u => u.provider === provider && u.providerId === profile.id);

  if (existingUser) return existingUser;

  const newUser = {
    id: Date.now(),
    provider,
    providerId: profile.id,
    email: profile.emails?.[0]?.value || '',
    name: profile.displayName || `${profile.name?.givenName || ''} ${profile.name?.familyName || ''}`.trim(),
    registeredAt: new Date().toISOString()
  };

  users.push(newUser);
  await fs.promises.writeFile(USERS_FILE, JSON.stringify(users, null, 2));
  return newUser;
}

// ========== PASSPORT STRATEGIES ==========
passport.use(new GoogleStrategy({
  clientID: process.env.GOOGLE_CLIENT_ID || '',
  clientSecret: process.env.GOOGLE_CLIENT_SECRET || '',
  callbackURL: 'http://localhost:3000/auth/google/callback'
}, async (accessToken, refreshToken, profile, done) => {
  try {
    const user = await handleSocialUser(profile, 'google');
    done(null, user);
  } catch (err) {
    done(err);
  }
}));

passport.use(new FacebookStrategy({
  clientID: process.env.FACEBOOK_CLIENT_ID || '',
  clientSecret: process.env.FACEBOOK_CLIENT_SECRET || '',
  callbackURL: 'http://localhost:3000/auth/facebook/callback',
  profileFields: ['id', 'emails', 'name', 'displayName']
}, async (accessToken, refreshToken, profile, done) => {
  try {
    const user = await handleSocialUser(profile, 'facebook');
    done(null, user);
  } catch (err) {
    done(err);
  }
}));

passport.use(new LinkedInStrategy({
  clientID: process.env.LINKEDIN_CLIENT_ID || '',
  clientSecret: process.env.LINKEDIN_CLIENT_SECRET || '',
  callbackURL: 'http://localhost:3000/auth/linkedin/callback',
  scope: ['r_liteprofile', 'r_emailaddress'],
  state: true
}, async (accessToken, refreshToken, profile, done) => {
  try {
    const user = await handleSocialUser(profile, 'linkedin');
    done(null, user);
  } catch (err) {
    done(err);
  }
}));

passport.serializeUser((user, done) => done(null, user.id));
passport.deserializeUser(async (id, done) => {
  const users = JSON.parse(await fs.promises.readFile(USERS_FILE, 'utf8'));
  done(null, users.find(u => u.id === id));
});

// ========== AUTH ROUTES ==========
app.get('/auth/google', passport.authenticate('google', { scope: ['profile', 'email'] }));
app.get('/auth/facebook', passport.authenticate('facebook', { scope: ['email'] }));
app.get('/auth/linkedin', passport.authenticate('linkedin'));

app.get('/auth/google/callback', passport.authenticate('google', { failureRedirect: '/login' }), (req, res) => res.redirect('/index.html'));
app.get('/auth/facebook/callback', passport.authenticate('facebook', { failureRedirect: '/login' }), (req, res) => res.redirect('/index.html'));
app.get('/auth/linkedin/callback', passport.authenticate('linkedin', { failureRedirect: '/login' }), (req, res) => res.redirect('/index.html'));

app.get('/logout', (req, res) => {
  req.logout(() => res.redirect('/login'));
});

app.get('/api/me', (req, res) => {
  if (!req.user) return res.status(401).json({ error: 'Not authenticated' });
  res.json({ id: req.user.id, name: req.user.name, email: req.user.email });
});

// ========== REGISTER & LOGIN ==========
app.post('/register', (req, res) => {
  const newUser = req.body;

  fs.readFile(USERS_FILE, (err, data) => {
    if (err) return res.status(500).send('Server error');

    const users = JSON.parse(data);
    if (users.some(user => user.email === newUser.email)) {
      return res.status(400).json({ error: 'Email already exists' });
    }

    users.push({
      id: Date.now(),
      ...newUser,
      registeredAt: new Date().toISOString()
    });

    fs.writeFile(USERS_FILE, JSON.stringify(users, null, 2), err => {
      if (err) return res.status(500).send('Server error');
      res.json({ success: true });
    });
  });
});

app.post('/login', async (req, res) => {
  try {
    const { email, password } = req.body;
    const users = JSON.parse(await fs.promises.readFile(USERS_FILE, 'utf8'));

    const user = users.find(u => u.email === email && u.password === password);
    if (!user) return res.status(401).json({ error: 'Invalid email or password' });

    res.json({
      success: true,
      redirect: '/index.html',
      user: { id: user.id, name: user.name }
    });
  } catch (err) {
    console.error('Login error:', err);
    res.status(500).json({ error: 'Server error' });
  }
});

// ========== CONTACT FORM ==========
app.post('/contact', async (req, res) => {
    console.log("Received contact request:", req.body);
  const requiredFields = ['name_contact', 'email_contact', 'message_contact', 'verify_contact'];
  
  try {
    // Validate fields
    const missingFields = requiredFields.filter(field => !req.body[field]?.trim());
    if (missingFields.length > 0) {
      return res.status(400).json({ 
        error: 'Missing required fields',
        fields: missingFields 
      });
    }

    // Verify answer
    if (req.body.verify_contact.trim() !== '4') {
      return res.status(400).json({ 
        error: 'Verification failed',
        message: 'Please enter 4 in the verification field' 
      });
    }

    // Save contact
    const contacts = JSON.parse(await fs.promises.readFile(CONTACTS_FILE, 'utf8'));
    
    contacts.push({
      id: Date.now(),
      name: req.body.name_contact,
      lastname: req.body.lastname_contact,
      email: req.body.email_contact,
      phone: req.body.phone_contact || '',
      message: req.body.message_contact,
      submittedAt: new Date().toISOString()
    });

    await fs.promises.writeFile(CONTACTS_FILE, JSON.stringify(contacts, null, 2));
    res.status(200).json({ success: 'Message saved successfully!' });

  } catch (err) {
    console.error('Contact form error:', err);
    res.status(500).json({ 
      error: 'Server error',
      details: err.message 
    });
  }
});

// ========== APPOINTMENT BOOKING ==========
// Add this before static files middleware
// In server.js
app.post('/api/appointments', async (req, res) => {
    try {
        const requiredFields = [
            'firstname', 'lastname', 'email',
            'doctor', 'date', 'time', 'treatments'
        ];

        // Validate required fields
        const missingFields = requiredFields.filter(field => !req.body[field]);
        if (missingFields.length > 0) {
            return res.status(400).json({
                error: 'Missing required fields',
                fields: missingFields
            });
        }

        // Read and update appointments
        const appointments = JSON.parse(await fs.promises.readFile(APPOINTMENTS_FILE));
        const newAppointment = {
            id: Date.now(),
            ...req.body
        };
        appointments.push(newAppointment);

        // Write to file
        await fs.promises.writeFile(APPOINTMENTS_FILE, JSON.stringify(appointments, null, 2));
        
        res.json({ success: true, appointment: newAppointment });

    } catch (err) {
        console.error('Appointment error:', err);
        res.status(500).json({ error: 'Server error: ' + err.message });
    }
});

// ========== FETCH USER BY ID ==========
app.get('/api/user', (req, res) => {
  const userId = parseInt(req.query.id);
  if (isNaN(userId)) return res.status(400).json({ error: 'Invalid user ID' });

  fs.readFile(USERS_FILE, 'utf8', (err, data) => {
    if (err) return res.status(500).json({ error: 'Server error' });

    const users = JSON.parse(data);
    const user = users.find(u => u.id === userId);
    if (!user) return res.status(404).json({ error: 'User not found' });

    res.json({ id: user.id, name: user.name, email: user.email, phone: user.phone || '' });
  });
});

// ========== SERVE STATIC FILES ==========
app.use(express.static(path.join(__dirname)));

// ========== START SERVER ==========
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});
