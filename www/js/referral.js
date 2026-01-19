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

  function extractMemberId(refCode) {
    // Extract numeric ID from ref code like 'm1' or 'member-1'
    const match = refCode.match(/\d+/);
    return match ? parseInt(match[0], 10) : null;
  }

  function init() {
    // Check for incoming referral parameter
    const refParam = getQueryParam('ref');
    
    if (refParam) {
      const memberId = extractMemberId(refParam);
      if (memberId) {
        // Store in localStorage
        try {
          localStorage.setItem('ipnz_incoming_ref', refParam);
          localStorage.setItem('ipnz_incoming_ref_id', memberId.toString());
          console.log('Referral captured:', refParam, 'ID:', memberId);
        } catch (e) {
          console.warn('Failed to store referral:', e);
        }
      }
    }

    // Populate hidden referrer_id field if on join page
    const referrerField = document.getElementById('referrer_id');
    if (referrerField) {
      try {
        const storedId = localStorage.getItem('ipnz_incoming_ref_id');
        if (storedId) {
          referrerField.value = storedId;
          console.log('Referrer ID populated in form:', storedId);
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
      const memberId = localStorage.getItem('ipnz_member_id');
      
      // Show banner if user was referred but hasn't joined
      if (incomingRef && !memberId) {
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
