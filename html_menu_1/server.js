const express = require('express');
const fs = require('fs');
const path = require('path');
const cors = require('cors');

const app = express();
const USERS_FILE = path.join(__dirname, 'users.json');


// ========== PASSPORT SETUP ==========
require('dotenv').config();
const session = require('express-session');
const passport = require('passport');
const GoogleStrategy = require('passport-google-oauth20').Strategy;
const FacebookStrategy = require('passport-facebook').Strategy;
const LinkedInStrategy = require('passport-linkedin-oauth2').Strategy;

// Add session middleware ABOVE passport initialization
app.use(session({
  secret: process.env.SESSION_SECRET,
  resave: false,
  saveUninitialized: true,
  cookie: { secure: false } // Change to true in production with HTTPS
}));

app.use(passport.initialize());
app.use(passport.session());

// ========== PASSPORT STRATEGIES ==========
// Helper function to find/create users
async function handleSocialUser(profile, provider) {
  const users = JSON.parse(await fs.promises.readFile(USERS_FILE, 'utf8'));
  
  const existingUser = users.find(u => 
    u.provider === provider && u.providerId === profile.id
  );

  if (existingUser) return existingUser;

  const newUser = {
    id: Date.now(),
    provider: provider,
    providerId: profile.id,
    email: profile.emails?.[0]?.value,
    name: profile.displayName || `${profile.name?.givenName} ${profile.name?.familyName}`,
    registeredAt: new Date().toISOString()
  };

  users.push(newUser);
  await fs.promises.writeFile(USERS_FILE, JSON.stringify(users, null, 2));
  return newUser;
}

// Google Strategy
passport.use(new GoogleStrategy({
  clientID: process.env.GOOGLE_CLIENT_ID,
  clientSecret: process.env.GOOGLE_CLIENT_SECRET,
  callbackURL: 'http://localhost:3000/auth/google/callback'
}, async (accessToken, refreshToken, profile, done) => {
  try {
    const user = await handleSocialUser(profile, 'google');
    done(null, user);
  } catch (err) {
    done(err);
  }
}));

// Facebook Strategy
passport.use(new FacebookStrategy({
  clientID: process.env.FACEBOOK_CLIENT_ID,
  clientSecret: process.env.FACEBOOK_CLIENT_SECRET,
  callbackURL: 'http://localhost:3000/auth/facebook/callback',
  profileFields: ['id', 'emails', 'name']
}, async (accessToken, refreshToken, profile, done) => {
  try {
    const user = await handleSocialUser(profile, 'facebook');
    done(null, user);
  } catch (err) {
    done(err);
  }
}));

// LinkedIn Strategy
passport.use(new LinkedInStrategy({
  clientID: process.env.LINKEDIN_CLIENT_ID,
  clientSecret: process.env.LINKEDIN_CLIENT_SECRET,
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

// ========== SERIALIZATION ==========
passport.serializeUser((user, done) => done(null, user.id));
passport.deserializeUser(async (id, done) => {
  const users = JSON.parse(await fs.promises.readFile(USERS_FILE, 'utf8'));
  done(null, users.find(u => u.id === id));
});

// ========== ADD THESE ROUTES ==========
// Social login routes
app.get('/auth/google', passport.authenticate('google', { scope: ['profile', 'email'] }));
app.get('/auth/facebook', passport.authenticate('facebook', { scope: ['email'] }));
app.get('/auth/linkedin', passport.authenticate('linkedin'));

// Social login callbacks
app.get('/auth/google/callback', 
  passport.authenticate('google', { failureRedirect: '/login' }),
  (req, res) => res.redirect('/index.html')
);

app.get('/auth/facebook/callback',
  passport.authenticate('facebook', { failureRedirect: '/login' }),
  (req, res) => res.redirect('/index.html')
);

app.get('/auth/linkedin/callback',
  passport.authenticate('linkedin', { failureRedirect: '/login' }),
  (req, res) => res.redirect('/index.html')
);

// Add logout route
app.get('/logout', (req, res) => {
  req.logout();
  res.redirect('/login');
});

// ========== MIDDLEWARE & INIT ==========
app.use(express.json());
app.use(cors());

// Initialize users.json
if (!fs.existsSync(USERS_FILE)) {
    fs.writeFileSync(USERS_FILE, '[]');
}
app.post('/login', async (req, res) => {
    try {
        const { email, password } = req.body;
        const data = await fs.promises.readFile(USERS_FILE, 'utf8');
        const users = JSON.parse(data);

        const user = users.find(u => u.email === email && u.password === password);
        
        if (!user) {
            return res.status(401).json({ error: 'Invalid email or password' });
        }

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
// Handle registration
app.post('/register', (req, res) => {
    const newUser = req.body;
    
    fs.readFile(USERS_FILE, (err, data) => {
        if (err) return res.status(500).send('Server error');
        
        const users = JSON.parse(data);
        
        // Check if email exists
        if (users.some(user => user.email === newUser.email)) {
            return res.status(400).json({ error: 'Email already exists' });
        }

        // Add new user
        users.push({
            id: Date.now(),
            ...newUser,
            registeredAt: new Date().toISOString()
        });

        fs.writeFile(USERS_FILE, JSON.stringify(users, null, 2), (err) => {
            if (err) return res.status(500).send('Server error');
            res.json({ success: true });
        });
    });
});

const APPOINTMENTS_FILE = path.join(__dirname, 'appointments.json');

// Initialize appointments.json
if (!fs.existsSync(APPOINTMENTS_FILE)) {
    fs.writeFileSync(APPOINTMENTS_FILE, '[]');
}

// New endpoint to get user data
app.get('/api/user', (req, res) => {
    const userId = JSON.parse(req.query.id);
    
    fs.readFile(USERS_FILE, (err, data) => {
        if (err) return res.status(500).json({ error: 'Server error' });
        const users = JSON.parse(data);
        const user = users.find(u => u.id === userId);
        res.json(user || {});
    });
});

// Save appointments
app.post('/api/appointments', (req, res) => {
    const newAppointment = req.body;
    
    fs.readFile(APPOINTMENTS_FILE, (err, data) => {
        if (err) return res.status(500).json({ error: 'Server error' });
        
        const appointments = JSON.parse(data);
        appointments.push({
            id: Date.now(),
            ...newAppointment,
            bookedAt: new Date().toISOString()
        });

        fs.writeFile(APPOINTMENTS_FILE, JSON.stringify(appointments, null, 2), (err) => {
            if (err) return res.status(500).json({ error: 'Server error' });
            res.json({ success: true });
        });
    });
});
// Add this endpoint before your static files middleware
app.get('/api/user', (req, res) => {
    try {
        const userId = parseInt(req.query.id);
        
        if (isNaN(userId)) {
            return res.status(400).json({ error: 'Invalid user ID format' });
        }

        fs.readFile(USERS_FILE, 'utf8', (err, data) => {
            if (err) {
                console.error('Users file read error:', err);
                return res.status(500).json({ error: 'Server error' });
            }

            const users = JSON.parse(data || '[]');
            const user = users.find(u => u.id === userId);

            if (!user) {
                console.log(`User not found for ID: ${userId}`);
                return res.status(404).json({ error: 'User not found' });
            }

            // Return only necessary user data
            res.json({
                id: user.id,
                name: user.name,
                email: user.email,
                phone: user.phone || ''
            });
        });
    } catch (err) {
        console.error('API Error:', err);
        res.status(500).json({ error: 'Server error' });
    }
});
// ========== STATIC FILES ==========
app.use(express.static(path.join(__dirname)));

// ========== START SERVER ==========
const PORT = 3000;
app.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
});