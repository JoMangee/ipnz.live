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
    return `<svg viewBox="0 0 300 500" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Map of New Zealand showing meetup locations" style="width: 100%; max-width: 400px; height: auto; margin: 0 auto; display: block;">
            <title>New Zealand Meetup Locations</title>
            <desc>Interactive map showing IPnz.live meetup locations across New Zealand</desc>
            
            <!-- Background with ocean gradient -->
            <rect width="300" height="500" fill="#e3f2fd"></rect>
            <defs>
                <linearGradient id="oceanGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#e3f2fd;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#bbdefb;stop-opacity:1" />
                </linearGradient>
            </defs>
            
            <!-- North Island - More accurate shape -->
            <path d="M 140,65 
                     L 160,55 L 180,52 L 195,55 L 205,65 L 210,80
                     L 212,100 L 210,120 L 205,140 L 198,160
                     L 188,175 L 175,185 L 160,190 L 145,190
                     L 130,185 L 115,175 L 105,160 L 100,140
                     L 98,120 L 100,100 L 105,80 L 115,65
                     L 125,58 L 140,55 Z" 
                  fill="#4CAF50" stroke="#2E7D32" stroke-width="2" opacity="0.9">
                <title>North Island / Te Ika-a-Māui</title>
            </path>
            
            <!-- Coromandel Peninsula -->
            <path d="M 195,55 
                     L 210,48 L 220,55 L 225,68 L 220,80
                     L 205,65 L 195,55 Z" 
                  fill="#66BB6A" stroke="#388E3C" stroke-width="1.5" opacity="0.9">
                <title>Coromandel Peninsula</title>
            </path>
            
            <!-- East Cape -->
            <path d="M 212,100 
                     L 220,95 L 228,105 L 230,120 L 228,130
                     L 222,120 L 215,110 L 212,100 Z" 
                  fill="#66BB6A" stroke="#388E3C" stroke-width="1.5" opacity="0.9">
                <title>East Cape</title>
            </path>
            
            <!-- Wellington Harbour (indentation) -->
            <path d="M 145,190 
                     L 140,205 L 135,200 L 130,195
                     L 135,190 Z" 
                  fill="#81C784" stroke="#2E7D32" stroke-width="1" opacity="0.9">
                <title>Wellington Harbour</title>
            </path>
            
            <!-- South Island - More accurate shape -->
            <path d="M 130,210 
                     L 145,205 L 160,210 L 175,225 L 185,245
                     L 190,265 L 192,285 L 193,305 L 192,325
                     L 190,345 L 186,365 L 180,385 L 173,405
                     L 165,425 L 155,440 L 145,450 L 135,455
                     L 125,450 L 115,440 L 105,425 L 98,405
                     L 92,385 L 88,365 L 85,345 L 84,325
                     L 85,305 L 87,285 L 90,265 L 95,245
                     L 105,225 L 115,210 Z" 
                  fill="#4CAF50" stroke="#2E7D32" stroke-width="2" opacity="0.9">
                <title>South Island / Te Waipounamu</title>
            </path>
            
            <!-- Marlborough Sounds (top of South Island) -->
            <path d="M 130,210 
                     L 120,205 L 110,200 L 105,195 L 110,190
                     L 120,195 L 130,200 Z" 
                  fill="#66BB6A" stroke="#388E3C" stroke-width="1.5" opacity="0.9">
                <title>Marlborough Sounds</title>
            </path>
            
            <!-- Banks Peninsula (Christchurch area) -->
            <path d="M 165,335 
                     L 175,330 L 185,335 L 180,345 L 170,340 Z" 
                  fill="#66BB6A" stroke="#388E3C" stroke-width="1.5" opacity="0.9">
                <title>Banks Peninsula</title>
            </path>
            
            <!-- Fiordland (bottom left of South Island) -->
            <path d="M 135,455 
                     L 125,460 L 115,465 L 110,470 L 115,475
                     L 125,470 L 135,465 Z" 
                  fill="#66BB6A" stroke="#388E3C" stroke-width="1.5" opacity="0.9">
                <title>Fiordland</title>
            </path>
            
            <!-- Stewart Island/Rakiura -->
            <path d="M 140,480 
                     L 155,475 L 170,480 L 165,490 L 150,495
                     L 135,490 Z" 
                  fill="#66BB6A" stroke="#2E7D32" stroke-width="1.5" opacity="0.9">
                <title>Stewart Island / Rakiura</title>
            </path>
            
            <!-- Cook Strait (between islands) -->
            <path d="M 145,190 
                     C 150,195 150,200 145,205
                     L 130,210" 
                  fill="none" stroke="#2196F3" stroke-width="0.5" stroke-dasharray="3,3">
                <title>Cook Strait / Te Moana-o-Raukawa</title>
            </path>
            
            <!-- Auckland marker -->
            <g id="marker-auckland" class="map-marker" data-location="auckland" role="button" tabindex="0" aria-label="Auckland meetup location" style="cursor: pointer;">
                <circle cx="170" cy="80" r="8" fill="#F8CB2E" stroke="#EE5007" stroke-width="2"></circle>
                <text x="170" y="65" text-anchor="middle" font-size="12" font-weight="bold" fill="#1a237e">Auckland</text>
            </g>
            
            <!-- Hamilton marker -->
            <g id="marker-hamilton" class="map-marker" data-location="hamilton" role="button" tabindex="0" aria-label="Hamilton meetup location" style="cursor: pointer;">
                <circle cx="160" cy="120" r="6" fill="#F8CB2E" stroke="#EE5007" stroke-width="2"></circle>
                <text x="160" y="107" text-anchor="middle" font-size="11" fill="#1a237e">Hamilton</text>
            </g>
            
            <!-- Wellington marker -->
            <g id="marker-wellington" class="map-marker" data-location="wellington" role="button" tabindex="0" aria-label="Wellington meetup location" style="cursor: pointer;">
                <circle cx="145" cy="195" r="8" fill="#F8CB2E" stroke="#EE5007" stroke-width="2"></circle>
                <text x="145" y="212" text-anchor="middle" font-size="12" font-weight="bold" fill="#1a237e">Wellington</text>
            </g>
            
            <!-- Christchurch marker -->
            <g id="marker-christchurch" class="map-marker" data-location="christchurch" role="button" tabindex="0" aria-label="Christchurch meetup location" style="cursor: pointer;">
                <circle cx="165" cy="335" r="8" fill="#F8CB2E" stroke="#EE5007" stroke-width="2"></circle>
                <text x="165" y="352" text-anchor="middle" font-size="12" font-weight="bold" fill="#1a237e">Christchurch</text>
            </g>
            
            <!-- Dunedin marker -->
            <g id="marker-dunedin" class="map-marker" data-location="dunedin" role="button" tabindex="0" aria-label="Dunedin meetup location" style="cursor: pointer;">
                <circle cx="148" cy="410" r="6" fill="#F8CB2E" stroke="#EE5007" stroke-width="2"></circle>
                <text x="148" y="427" text-anchor="middle" font-size="11" fill="#1a237e">Dunedin</text>
            </g>
            
            <!-- Scale indicator -->
            <g font-size="10" fill="#424242">
                <line x1="20" y1="470" x2="50" y2="470" stroke="#424242" stroke-width="2"></line>
                <text x="35" y="465" text-anchor="middle">~300 km</text>
            </g>
            
            <!-- Compass rose -->
            <g transform="translate(270, 430)">
                <circle cx="0" cy="0" r="20" fill="none" stroke="#424242" stroke-width="1"></circle>
                <text x="0" y="-25" text-anchor="middle" font-size="10" fill="#424242">N</text>
                <text x="25" y="0" text-anchor="middle" font-size="10" fill="#424242">E</text>
                <text x="0" y="25" text-anchor="middle" font-size="10" fill="#424242">S</text>
                <text x="-25" y="0" text-anchor="middle" font-size="10" fill="#424242">W</text>
                <line x1="0" y1="-15" x2="0" y2="15" stroke="#424242" stroke-width="2"></line>
                <line x1="-15" y1="0" x2="15" y2="0" stroke="#424242" stroke-width="2"></line>
            </g>
        </svg>`;
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
