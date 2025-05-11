// Function to fetch doctors data
async function fetchDoctors() {
  try {
    const response = await fetch('doctors.json');
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    const data = await response.json();
    return data.doctors;
  } catch (error) {
    console.error('Error fetching doctors:', error);
    return [];
  }
}

// Function to render doctor tiles in grid-view.html
async function renderDoctorTiles() {
  const doctors = await fetchDoctors();
  const gridContainer = document.querySelector('.row'); // The container where doctor tiles should be placed
  
  if (!gridContainer || !doctors.length) return;
  
  gridContainer.innerHTML = ''; // Clear existing content
  
  doctors.forEach(doctor => {
    const doctorTile = document.createElement('div');
    doctorTile.className = 'col-md-6';
    doctorTile.innerHTML = `
      <div class="box_list wow fadeIn">
        <a href="#0" class="wish_bt"></a>
        <figure>
          <a href="detail-page.html?id=${doctor.id}">
            <img src="${doctor.image}" class="img-fluid" alt="${doctor.name}" />
            <div class="preview"><span>Read more</span></div>
          </a>
        </figure>
        <div class="wrapper">
          <small>${doctor.specialty}</small>
          <h3>${doctor.name}</h3>
          <p>${doctor.qualifications}</p>
          <span class="rating">
            <i class="icon_star voted"></i>
            <i class="icon_star voted"></i>
            <i class="icon_star voted"></i>
            <i class="icon_star"></i>
            <i class="icon_star"></i>
            <small>(145)</small>
          </span>
          <a href="badges.html" data-bs-toggle="tooltip" data-bs-placement="top" title="Badge Level" class="badge_list_1">
            <img src="img/badges/badge_1.svg" width="15" height="15" alt="" />
          </a>
        </div>
        <ul>
          <li>
            <a href="#0" onclick="onHtmlClick('Doctors', 0)">
              <i class="icon_pin_alt"></i>View on map
            </a>
          </li>
          <li>
            <a href="${doctors.hospital.map_link}" target="_blank">
              <i class="icon_pin_alt"></i>Directions
            </a>
          </li>
          <li><a href="detail-page.html?id=${doctor.id}">Book now</a></li>
        </ul>
      </div>
    `;
    gridContainer.appendChild(doctorTile);
  });
}

// Function to render doctor details in detail-page.html
async function renderDoctorDetails() {
  const urlParams = new URLSearchParams(window.location.search);
  const doctorId = urlParams.get('id');
  
  if (!doctorId) return;
  
  const doctors = await fetchDoctors();
  const doctor = doctors.find(d => d.id == doctorId);
  
  if (!doctor) return;
  
  // Update doctor info in sidebar
  document.querySelector('.box_profile figure img').src = doctor.image;
  document.querySelector('.box_profile small').textContent = doctor.specialty;
  document.querySelector('.box_profile h1').textContent = doctor.name;
  document.querySelector('.box_profile .contacts li:nth-child(1)').innerHTML = `
    <h6>Address</h6>
    ${doctor.hospital}, <br />
    ${doctor.address}
  `;
  document.querySelector('.box_profile .contacts li:nth-child(2)').innerHTML = `
    <h6>Phone</h6>
    <a href="tel://${doctor.appointment.phone.replace(/\+/g, '')}">${doctor.appointment.phone}</a>
  `;
  
  // Update general info section
  document.querySelector('.lead').textContent = `${doctor.name} is a renowned ${doctor.specialty.toLowerCase()} in Bangladesh with extensive experience.`;
  
  // Update education section
  const educationList = document.querySelector('.list_edu');
  educationList.innerHTML = `
    <li><strong>${doctor.qualifications.split(', ')[0]}</strong> - ${doctor.qualifications.split(', ').slice(1).join(', ')}</li>
  `;
  
  // Update prices table if needed
}

// Call the appropriate function based on the page
document.addEventListener('DOMContentLoaded', function() {
  if (window.location.pathname.includes('grid-list.html')) {
    renderDoctorTiles();
  } else if (window.location.pathname.includes('detail-page.html')) {
    renderDoctorDetails();
  }
});