/* Gold Bullion Shop: photo gallery, gold calculator, checkout total */
(function(){
  var fmt = function(v){ return '$' + v.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); };

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
