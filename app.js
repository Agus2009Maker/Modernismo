document.addEventListener('DOMContentLoaded', () => {

  /**
   * MEJORA MÓVIL: Detectar si es un dispositivo táctil
   */
  const isTouchDevice = () => 'ontouchstart' in window || navigator.maxTouchPoints > 0;

  /**
   * MEJORA 1: PRELOADER
   */
  window.addEventListener('load', () => {
    const preloader = document.getElementById('preloader');
    if (preloader) {
      const preloaderTime = getComputedStyle(document.documentElement).getPropertyValue('--preloader-time').trim() || '1.5s';
      setTimeout(() => {
        preloader.classList.add('loaded');
      }, parseFloat(preloaderTime) * 1000 * 0.5);
    }
  });

  /**
   * MEJORA 2: CURSOR PERSONALIZADO
   * MEJORA MÓVIL: Solo se ejecuta si NO es táctil.
   */
  const cursor = document.getElementById('custom-cursor');
  if (cursor && !isTouchDevice()) { // <-- ¡Aquí está la comprobación!
    document.addEventListener('mousemove', (e) => {
      cursor.style.transform = `translate(${e.clientX}px, ${e.clientY}px)`;
    });

    const interactiveElements = document.querySelectorAll('a, button, .author-card, .card, .spy-dot');
    interactiveElements.forEach(el => {
      el.addEventListener('mouseenter', () => cursor.classList.add('hover'));
      el.addEventListener('mouseleave', () => cursor.classList.remove('hover'));
    });
  }

  /**
   * ANIMACIÓN DEL HÉROE (Fragmentación)
   */
  const heroTitle = document.getElementById('hero-title');
  if (heroTitle) {
    const text = heroTitle.textContent;
    heroTitle.innerHTML = '';
    
    text.split('').forEach(char => {
      const span = document.createElement('span');
      span.textContent = char;
      if (char === ' ') span.style.width = '1rem'; 
      heroTitle.appendChild(span);
    });

    const spans = heroTitle.querySelectorAll('span');
    spans.forEach((span, index) => {
      const randomX = (Math.random() - 0.5) * 500;
      const randomY = (Math.random() - 0.5) * 500;
      const randomRot = (Math.random() - 0.5) * 90;
      span.style.transform = `translate(${randomX}px, ${randomY}px) rotate(${randomRot}deg)`;
      span.style.opacity = '0';
    });

    setTimeout(() => {
      spans.forEach((span, index) => {
        span.style.transitionDelay = `${index * 50}ms`;
        span.style.transform = 'translate(0, 0) rotate(0deg)';
        span.style.opacity = '1';
      });
      setTimeout(() => heroTitle.classList.add('assembled'), (spans.length * 50) + 500);
    }, 100); 
  }

  /**
   * ANIMACIÓN GENERAL (Scroll-In-View)
   */
  const inViewObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('in-view');
        inViewObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.animate-on-scroll').forEach(el => {
    inViewObserver.observe(el);
  });

  /**
   * ANIMACIÓN DE SINESTESIA (Hover en Tarjeta)
   * MEJORA MÓVIL: Esta animación de 'hover' no se ejecutará en táctil,
   * lo cual está bien. El CSS (pointer: fine) también ayuda a
   * deshabilitar efectos de hover innecesarios en el Glitch.
   */
  const sinestesiaCard = document.getElementById('sinestesia-card');
  const streamContainer = sinestesiaCard ? sinestesiaCard.querySelector('.stream-animation-container') : null;
  
  if (sinestesiaCard && streamContainer && !isTouchDevice()) { // <-- Solo añadir listener en no-táctil
    const quote = "y el verso, que es la lira, tiene el alma del mármol, la armonía del astro, la luz de la idea...";
    const words = quote.split(' ');
    let streamTimer; 

    sinestesiaCard.addEventListener('mouseenter', () => {
      streamContainer.innerHTML = ''; 
      words.forEach((word, index) => {
        const span = document.createElement('span');
        span.textContent = word + ' ';
        streamContainer.appendChild(span);
        setTimeout(() => {
          span.style.opacity = '0.7';
          span.style.transform = 'translateY(0)';
        }, index * 100);
      });
    });

    sinestesiaCard.addEventListener('mouseleave', () => {
      clearTimeout(streamTimer);
      const spans = streamContainer.querySelectorAll('span');
      spans.forEach((span, index) => {
        setTimeout(() => {
          span.style.opacity = '0';
          span.style.transform = 'translateY(10px)';
        }, index * 30); 
      });
    });
  }

  /**
   * MEJORA 3: LÓGICA DEL SCROLL-SPY
   * MEJORA MÓVIL: No se ejecuta si es táctil.
   */
  const spyDots = document.querySelectorAll('#scroll-spy .spy-dot');
  if (spyDots.length > 0 && !isTouchDevice()) {
    const sections = document.querySelectorAll('section[data-section-name], header[data-section-name]');

    const spyObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
          const sectionId = entry.target.id;
          spyDots.forEach(dot => dot.classList.remove('active'));
          const activeDot = document.querySelector(`#scroll-spy a[href="#${sectionId}"]`);
          if (activeDot) {
            activeDot.classList.add('active');
          }
        }
      });
    }, { 
      threshold: 0.5,
      rootMargin: '0px 0px -50% 0px'
    });

    sections.forEach(section => {
      spyObserver.observe(section);
    });
    
    spyDots.forEach(dot => {
      dot.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        const targetSection = document.querySelector(targetId);
        if (targetSection) {
          targetSection.scrollIntoView({ behavior: 'smooth' });
        }
      });
    });
  }

});
