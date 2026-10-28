/* Success confetti in the Zambian flag colours — the single celebratory moment. */
window.zpConfetti = function (host) {
  if (!host || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  const colours = ['#198A00', '#DE2010', '#000000', '#EF7D00'];
  for (let i = 0; i < 40; i++) {
    const p = document.createElement('i');
    p.style.left = Math.random() * 100 + '%';
    p.style.background = colours[i % colours.length];
    p.style.animationDelay = Math.random() * 0.4 + 's';
    host.appendChild(p);
  }
  setTimeout(function () { host.innerHTML = ''; }, 2600);
};
document.addEventListener('DOMContentLoaded', function () {
  window.zpConfetti(document.querySelector('.zp-result--paid .zp-confetti'));
});
