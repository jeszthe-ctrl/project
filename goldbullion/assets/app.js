/* Gold Bullion Shop: photo gallery, gold calculator, checkout total */
(function(){
  var fmt = function(v){ return '$' + v.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); };

  /* phone menu button */
  var mb = document.querySelector('.menu-btn');
  if(mb) mb.addEventListener('click', function(){ var n = mb.parentNode, o = !n.classList.contains('open'); n.classList.toggle('open', o); mb.setAttribute('aria-expanded', o ? 'true' : 'false'); });

  /* menu: carets open their dropdown (needed on touch screens and phones) */
  document.querySelectorAll('.caret').forEach(function(b){
    b.addEventListener('click', function(e){
      e.preventDefault();
      var li = b.parentNode, open = !li.classList.contains('open');
      document.querySelectorAll('.has-sub.open').forEach(function(x){ if(x !== li){ x.classList.remove('open'); x.querySelector('.caret').setAttribute('aria-expanded', 'false'); } });
      li.classList.toggle('open', open); b.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });
  document.addEventListener('click', function(e){ if(!e.target.closest('.has-sub')) document.querySelectorAll('.has-sub.open').forEach(function(x){ x.classList.remove('open'); }); });
  document.addEventListener('keydown', function(e){ if(e.key === 'Escape') document.querySelectorAll('.has-sub.open').forEach(function(x){ x.classList.remove('open'); }); });

  /* filters: "show all" options; on phones the filter panel starts closed */
  document.querySelectorAll('.facet-more').forEach(function(b){ b.addEventListener('click', function(){ b.parentNode.classList.add('all'); b.remove(); }); });
  if(window.matchMedia('(max-width:1000px)').matches) document.querySelectorAll('.filters-box').forEach(function(d){ if(!/[?&](metal|type|w|kind|brand|year)=/.test(location.search)) d.removeAttribute('open'); });

  /* price chart: range tabs and hover / touch read-out */
  document.querySelectorAll('.pchart').forEach(function(c){
    c.querySelectorAll('.ptabs button').forEach(function(b){
      b.addEventListener('click', function(){
        c.querySelectorAll('.ptabs button').forEach(function(x){ x.setAttribute('aria-selected', x === b ? 'true' : 'false'); });
        c.querySelectorAll('.prange').forEach(function(r){ r.hidden = r.getAttribute('data-r') !== b.getAttribute('data-r'); });
      });
    });
    c.querySelectorAll('.chart-wrap').forEach(function(w){
      var svg = w.querySelector('svg'), tip = w.querySelector('.tip'), pts = JSON.parse(svg.getAttribute('data-pts') || '[]');
      var hx = svg.querySelector('.hx'), hd = svg.querySelector('.hd');
      var move = function(e){
        var r = svg.getBoundingClientRect(), cx = ((e.touches ? e.touches[0].clientX : e.clientX) - r.left) / r.width * svg.viewBox.baseVal.width, best = pts[0];
        pts.forEach(function(p){ if(Math.abs(p[0] - cx) < Math.abs(best[0] - cx)) best = p; });
        if(!best) return;
        hx.setAttribute('x1', best[0]); hx.setAttribute('x2', best[0]); hd.setAttribute('cx', best[0]); hd.setAttribute('cy', best[1]);
        tip.hidden = false; tip.innerHTML = '<b>' + best[3] + '</b>' + best[2];
        tip.style.left = Math.min(Math.max(best[0] / svg.viewBox.baseVal.width * r.width, 60), r.width - 60) + 'px';
        w.classList.add('on');
      };
      svg.addEventListener('mousemove', move); svg.addEventListener('touchmove', move, {passive: true}); svg.addEventListener('touchstart', move, {passive: true});
      svg.addEventListener('mouseleave', function(){ tip.hidden = true; w.classList.remove('on'); });
    });
  });

  /* product photos */
  document.querySelectorAll('.thumb').forEach(function(b){
    b.addEventListener('click', function(){
      var main = document.querySelector('.gal-main img');
      if(main){ main.src = b.getAttribute('data-src'); main.removeAttribute('srcset'); }
    });
  });

  /* gold value calculator: weight × grams per unit × spot per gram × karat/24 */
  document.querySelectorAll('.calc').forEach(function(c){
    var spot = parseFloat(c.getAttribute('data-spot')) || 0;
    var w = c.querySelector('.c-w'), u = c.querySelector('.c-u'), k = c.querySelector('.c-k'), out = c.querySelector('.c-v');
    var run = function(){
      var v = (parseFloat(w.value) || 0) * parseFloat(u.value) * spot / 31.1034768 * parseInt(k.value, 10) / 24;
      out.textContent = fmt(v);
    };
    [w, u, k].forEach(function(el){ el.addEventListener('input', run); el.addEventListener('change', run); });
    run();
  });

  /* checkout: shipping and total follow the chosen option */
  var form = document.getElementById('checkout');
  if(form){
    var goods = parseFloat((form.querySelector('[data-goods]') || {}).getAttribute ? form.querySelector('[data-goods]').getAttribute('data-goods') : 0) || 0;
    var update = function(){
      var r = form.querySelector('input[name=ship]:checked');
      if(!r) return;
      var cost = parseFloat(r.getAttribute('data-cost')) || 0;
      document.getElementById('ship-cost').textContent = cost > 0 ? fmt(cost) : 'Free';
      document.getElementById('grand').textContent = fmt(goods + cost);
    };
    form.querySelectorAll('input[name=ship]').forEach(function(r){ r.addEventListener('change', update); });
    update();
    form.addEventListener('submit', function(e){
      var phone = form.querySelector('[name=phone]');
      if(phone && phone.value.replace(/\D/g, '').length < 10){ e.preventDefault(); phone.focus(); phone.setCustomValidity('Please enter your phone number with area code.'); phone.reportValidity(); phone.setCustomValidity(''); return; }
      var bad = form.querySelector(':invalid');
      if(bad){ e.preventDefault(); bad.focus(); bad.reportValidity && bad.reportValidity(); return; }
      var btn = form.querySelector('button[type=submit]');
      if(btn){ btn.disabled = true; btn.textContent = 'Placing your order…'; }
    });
  }
})();
