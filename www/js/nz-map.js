/**
 * Privacy-Respecting New Zealand Map
 * Self-contained SVG map with no external dependencies
 */
(function() {
  'use strict';

  const meetupLocations = [
    {
      id: 'auckland',
      name: 'Auckland',
      region: 'Auckland Region',
      address: 'Aotea Square, Queen Street, Auckland CBD',
      coords: '174.7633,-36.8485',
      members: 0, // Will be populated from backend
      nextMeetup: 'TBA'
    },
    {
      id: 'wellington',
      name: 'Wellington',
      region: 'Wellington Region',
      address: 'Civic Square, 101 Wakefield Street, Wellington',
      coords: '174.7762,-41.2865',
      members: 0,
      nextMeetup: 'TBA'
    },
    {
      id: 'christchurch',
      name: 'Christchurch',
      region: 'Canterbury',
      address: 'Cathedral Square, Christchurch Central',
      coords: '172.6362,-43.5321',
      members: 0,
      nextMeetup: 'TBA'
    },
    {
      id: 'hamilton',
      name: 'Hamilton',
      region: 'Waikato',
      address: 'Garden Place, Hamilton Central',
      coords: '175.2793,-37.7870',
      members: 0,
      nextMeetup: 'TBA'
    },
    {
      id: 'dunedin',
      name: 'Dunedin',
      region: 'Otago',
      address: 'The Octagon, Dunedin',
      coords: '170.5028,-45.8788',
      members: 0,
      nextMeetup: 'TBA'
    }
  ];

  function createSVGMap() {
    // Simplified New Zealand SVG outline (North and South Islands)
    return `
      <svg viewBox="0 0 300 500" xmlns="http://www.w3.org/2000/svg" 
           role="img" aria-label="Map of New Zealand showing meetup locations"
           style="width: 100%; max-width: 400px; height: auto; margin: 0 auto; display: block;">
        <title>New Zealand Meetup Locations</title>
        <desc>Interactive map showing IPnz.live meetup locations across New Zealand</desc>
        
        <!-- Background -->
        <rect width="300" height="500" fill="#e3f2fd" />
        
        <!-- North Island -->
        <path d="M 150 50 
                 L 165 45 L 175 50 L 180 60 L 185 75 L 188 90 
                 L 190 110 L 188 130 L 185 150 L 180 170 
                 L 175 185 L 168 195 L 160 200 L 150 202
                 L 140 200 L 132 195 L 125 185 L 120 170
                 L 118 150 L 120 130 L 125 110 L 130 90
                 L 135 75 L 140 60 L 145 50 Z" 
              fill="#4CAF50" 
              stroke="#2E7D32" 
              stroke-width="2"
              opacity="0.9" />
        
        <!-- South Island -->
        <path d="M 145 230
                 L 155 228 L 165 230 L 175 240 L 180 260
                 L 182 280 L 183 300 L 182 320 L 180 340
                 L 175 360 L 170 380 L 165 400 L 158 420
                 L 150 435 L 142 445 L 135 448 L 128 445
                 L 122 435 L 118 420 L 115 400 L 113 380
                 L 112 360 L 113 340 L 115 320 L 118 300
                 L 120 280 L 122 260 L 125 240 L 130 230 Z" 
              fill="#4CAF50" 
              stroke="#2E7D32" 
              stroke-width="2"
              opacity="0.9" />
        
        <!-- Auckland marker -->
        <g id="marker-auckland" class="map-marker" data-location="auckland" 
           role="button" tabindex="0" aria-label="Auckland meetup location">
          <circle cx="170" cy="80" r="8" fill="#F8CB2E" stroke="#EE5007" stroke-width="2" />
          <text x="170" y="65" text-anchor="middle" font-size="12" font-weight="bold" fill="#1a237e">Auckland</text>
        </g>
        
        <!-- Hamilton marker -->
        <g id="marker-hamilton" class="map-marker" data-location="hamilton"
           role="button" tabindex="0" aria-label="Hamilton meetup location">
          <circle cx="160" cy="120" r="6" fill="#F8CB2E" stroke="#EE5007" stroke-width="2" />
          <text x="160" y="107" text-anchor="middle" font-size="11" fill="#1a237e">Hamilton</text>
        </g>
        
        <!-- Wellington marker -->
        <g id="marker-wellington" class="map-marker" data-location="wellington"
           role="button" tabindex="0" aria-label="Wellington meetup location">
          <circle cx="155" cy="210" r="8" fill="#F8CB2E" stroke="#EE5007" stroke-width="2" />
          <text x="155" y="227" text-anchor="middle" font-size="12" font-weight="bold" fill="#1a237e">Wellington</text>
        </g>
        
        <!-- Christchurch marker -->
        <g id="marker-christchurch" class="map-marker" data-location="christchurch"
           role="button" tabindex="0" aria-label="Christchurch meetup location">
          <circle cx="165" cy="335" r="8" fill="#F8CB2E" stroke="#EE5007" stroke-width="2" />
          <text x="165" y="352" text-anchor="middle" font-size="12" font-weight="bold" fill="#1a237e">Christchurch</text>
        </g>
        
        <!-- Dunedin marker -->
        <g id="marker-dunedin" class="map-marker" data-location="dunedin"
           role="button" tabindex="0" aria-label="Dunedin meetup location">
          <circle cx="148" cy="410" r="6" fill="#F8CB2E" stroke="#EE5007" stroke-width="2" />
          <text x="148" y="427" text-anchor="middle" font-size="11" fill="#1a237e">Dunedin</text>
        </g>
      </svg>
    `;
  }

  function createLocationDetails(location) {
    return `
      <div class="location-details" role="region" aria-label="Meetup location details">
        <h3>${location.name}</h3>
        <p class="region">${location.region}</p>
        <div class="address-box">
          <strong>Address:</strong>
          <p>${location.address}</p>
          <button class="copy-btn" data-copy="${location.address}" 
                  aria-label="Copy address to clipboard">
            <i class="bi bi-clipboard"></i> Copy Address
          </button>
        </div>
        <div class="coords-box">
          <strong>Coordinates:</strong>
          <p class="coords">${location.coords}</p>
          <button class="copy-btn" data-copy="${location.coords}"
                  aria-label="Copy coordinates to clipboard">
            <i class="bi bi-geo-alt"></i> Copy Coordinates
          </button>
        </div>
        ${location.members > 0 ? `<p class="members"><i class="bi bi-people"></i> ${location.members} members nearby</p>` : ''}
        ${location.nextMeetup !== 'TBA' ? `<p class="next-meetup"><i class="bi bi-calendar-event"></i> Next: ${location.nextMeetup}</p>` : ''}
      </div>
    `;
  }

  function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(() => {
        showToast('Copied to clipboard!');
      }).catch(err => {
        fallbackCopy(text);
      });
    } else {
      fallbackCopy(text);
    }
  }

  function fallbackCopy(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    document.body.appendChild(textArea);
    textArea.select();
    try {
      document.execCommand('copy');
      showToast('Copied to clipboard!');
    } catch (err) {
      showToast('Failed to copy. Please select and copy manually.');
    }
    document.body.removeChild(textArea);
  }

  function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'copy-toast';
    toast.textContent = message;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'polite');
    document.body.appendChild(toast);
    setTimeout(() => {
      toast.classList.add('show');
    }, 100);
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => document.body.removeChild(toast), 300);
    }, 2000);
  }

  function init() {
    const mapContainer = document.getElementById('nz-map-container');
    if (!mapContainer) return;

    // Create map structure
    mapContainer.innerHTML = `
      <div class="nz-map-wrapper">
        <div class="map-column">
          ${createSVGMap()}
        </div>
        <div class="details-column" id="location-details">
          <div class="placeholder">
            <i class="bi bi-cursor"></i>
            <p>Click on a location marker to see details</p>
          </div>
        </div>
      </div>
    `;

    // Add event listeners to markers
    const markers = mapContainer.querySelectorAll('.map-marker');
    markers.forEach(marker => {
      marker.style.cursor = 'pointer';
      
      marker.addEventListener('click', function() {
        const locationId = this.dataset.location;
        const location = meetupLocations.find(loc => loc.id === locationId);
        if (location) {
          showLocationDetails(location);
          // Remove active class from all markers
          markers.forEach(m => m.classList.remove('active'));
          // Add active class to clicked marker
          this.classList.add('active');
        }
      });

      // Keyboard accessibility
      marker.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          this.click();
        }
      });

      // Hover effect
      marker.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.2)';
        this.style.transition = 'transform 0.2s';
      });
      marker.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
      });
    });
  }

  function showLocationDetails(location) {
    const detailsContainer = document.getElementById('location-details');
    detailsContainer.innerHTML = createLocationDetails(location);

    // Add copy button listeners
    const copyButtons = detailsContainer.querySelectorAll('.copy-btn');
    copyButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        copyToClipboard(this.dataset.copy);
      });
    });
  }

  // Initialize on DOMContentLoaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
