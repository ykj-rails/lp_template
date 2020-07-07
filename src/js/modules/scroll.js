const scrollEvt = () => {
  const targetElm = document.querySelectorAll('a[href^="#"]');
  targetElm.forEach(el => {
    el.addEventListener('click', e => {
      const splitHref = e.currentTarget.getAttribute('href').split('#');
      const destID = splitHref[1];
      
      const destPos = document.getElementById(destID).getBoundingClientRect().top + window.pageYOffset;
      smoothScroll(destPos);
      e.preventDefault();
    })
  })
}

const smoothScroll = position => {
  let currentY = window.pageYOffset;
  const maxY = document.documentElement.scrollHeight - document.documentElement.clientHeight;
  const step = 60;
  if (currentY > position) step *= -1;

  (function scrollAnim() {
    currentY = window.pageYOffset + step;
    if((step > 0 && currentY > position) || (step < 0 && currentY < position) || currentY > maxY || currentY < 0){
      window.scrollTo(0, position)
      return false;
    } else {
      window.scrollTo(0, currentY)
      setTimeout(() => {
        scrollAnim();
      }, 1)
    }
  })();
}

export { scrollEvt };