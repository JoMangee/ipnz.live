(function(){
  var link = document.getElementById('share-x');
  if(!link) return;
  var base = 'https://IPnz.live';
  var params = new URLSearchParams(window.location.search);
  var ref = params.get('ref') || window.localStorage.getItem('ipnz_ref') || '';
  var url = ref ? base + '?ref=' + encodeURIComponent(ref) : base;
  var text = "I've just heard about the #InternetPartyPeople";
  var via = 'thegeekjo';
  var hashtags = 'InternetPartyPeople,IPnz';

  // Preferred X endpoint
  var xIntent = 'https://x.com/intent/tweet?text=' + encodeURIComponent(text)
               + '&url=' + encodeURIComponent(url)
               + '&via=' + encodeURIComponent(via)
               + '&hashtags=' + encodeURIComponent(hashtags);

  // Graceful fallback to legacy twitter.com
  var twIntent = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(text)
               + '&url=' + encodeURIComponent(url)
               + '&via=' + encodeURIComponent(via)
               + '&hashtags=' + encodeURIComponent(hashtags);

  // Scripted open avoids overlay quirks and preserves prefill.
  link.addEventListener('click', function(e){
    e.preventDefault();
    var w = null;
    try {
      w = window.open(xIntent, 'ipnz-share', 'noopener');
    } catch(_) {}
    // If popup blocked or null, try fallback
    if (!w) {
      try {
        window.open(twIntent, 'ipnz-share', 'noopener');
      } catch(__) {
        // Final fallback: navigate current tab
        window.location.href = twIntent;
      }
    }
  });
})();