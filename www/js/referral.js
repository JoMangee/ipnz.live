/**
 * Referral System
 * Captures ?ref= parameter, stores in localStorage, and populates form
 */
(function() {
  'use strict';

  function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
  }

  function extractReferralCode(refCode) {
    // Validate alphanumeric referral code (6 chars, e.g., 'A3X9K2')
    const cleaned = refCode.toUpperCase().trim();
    // Also accept old format 'm{id}' for backwards compatibility with migrated members
    if (cleaned.startsWith('M')) {
      return cleaned.substring(1);
    }
    // New format: 6-char alphanumeric
    return /^[A-Z0-9]{6}$/.test(cleaned) ? cleaned : null;
  }

  function init() {
    // Check for incoming referral parameter
    const refParam = getQueryParam('ref');
    
    if (refParam) {
      const referralCode = extractReferralCode(refParam);
      if (referralCode) {
        // Store in localStorage
        try {
          localStorage.setItem('ipnz_incoming_ref', referralCode);
          console.log('Referral captured:', referralCode);
        } catch (e) {
          console.warn('Failed to store referral:', e);
        }
      }
    }

    // Populate hidden referrer_code field if on join page
    const referrerField = document.getElementById('referrer_code');
    if (referrerField) {
      try {
        const storedCode = localStorage.getItem('ipnz_incoming_ref');
        if (storedCode) {
          referrerField.value = storedCode;
          console.log('Referrer code populated in form:', storedCode);
        }
      } catch (e) {
        console.warn('Failed to populate referrer:', e);
      }
    }

    // Show join prompt banner if user was referred and hasn't joined yet
    showReferralPrompt();
  }

  function showReferralPrompt() {
    // Only show on homepage or auth pages, not on join page itself
    if (window.location.pathname.includes('/join')) {
      return;
    }

    try {
      const incomingRef = localStorage.getItem('ipnz_incoming_ref');
      const memberUuid = localStorage.getItem('ipnz_member_uuid');
      
      // Show banner if user was referred but hasn't joined
      if (incomingRef && !memberUuid) {
        const banner = document.getElementById('referral-banner');
        if (banner) {
          banner.style.display = 'block';
          
          // Handle join button click
          const joinBtn = document.getElementById('referral-join-btn');
          if (joinBtn) {
            joinBtn.addEventListener('click', function(e) {
              e.preventDefault();
              window.location.href = '/join';
            });
          }

          // Handle dismiss
          const dismissBtn = document.getElementById('referral-dismiss');
          if (dismissBtn) {
            dismissBtn.addEventListener('click', function(e) {
              e.preventDefault();
              banner.style.display = 'none';
              localStorage.setItem('ipnz_ref_banner_dismissed', 'true');
            });
          }
        }
      }
    } catch (e) {
      console.warn('Failed to show referral prompt:', e);
    }
  }

  // Initialize on DOMContentLoaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
