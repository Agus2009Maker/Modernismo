document.addEventListener('DOMContentLoaded', () => {

  /**
   * MEJORA 1: PRELOADER
   * Oculta el preloader cuando la página (incluyendo imágenes) ha terminado de cargar.
   */
  window.addEventListener('load', () => {
    const preloader = document.getElementById('preloader');
    if (preloader) {
      // Usa el tiempo de la variable CSS para la transición
      const preloaderTime = getComputedStyle(document.documentElement).getPropertyValue('--preloader-time').trim() || '1.5s';
      // Espera a que termine la animación mínima
      setTimeout(() => {
        preloader.classList.add('loaded');
      }, parseFloat(preloaderTime) * 1000 * 0.5); // Espera al menos la mitad del tiempo de anim.
    }
  });

  /**
   * MEJORA 2: CURSOR PERSONALIZADO
   * Mueve el div #custom-cursor para que siga al ratón.
   */
  const cursor = document.getElementById('custom-cursor');
  if (cursor) {
    document.addEventListener('mousemove', (e) => {
      cursor.style.transform = `translate(${e.clientX}px, ${e.clientY}px)`;
    });

    // Añadir clase 'hover' al pasar sobre elementos interactivos
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
      // Añadir clase para la animación de brillo
      setTimeout(() => heroTitle.classList.add('assembled'), (spans.length * 50) + 500);
    }, 100); 
  }

  /**
   * ANIMACIÓN GENERAL (Scroll-In-View)
   * Observador para la clase .animate-on-scroll
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
   */
  const sinestesiaCard = document.getElementById('sinestesia-card');
  const streamContainer = sinestesiaCard ? sinestesiaCard.querySelector('.stream-animation-container') : null;
  
  if (sinestesiaCard && streamContainer) {
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
   */
  const spyDots = document.querySelectorAll('#scroll-spy .spy-dot');
  const sections = document.querySelectorAll('section[data-section-name], header[data-section-name]');

  // Observador para el Scroll-Spy (diferente del de 'in-view')
  const spyObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
        const sectionId = entry.target.id;
        // Quitar 'active' de todos los puntos
        spyDots.forEach(dot => dot.classList.remove('active'));
        // Añadir 'active' al punto correspondiente
        const activeDot = document.querySelector(`#scroll-spy a[href="#${sectionId}"]`);
        if (activeDot) {
          activeDot.classList.add('active');
        }
      }
    });
  }, { 
    threshold: 0.5, // Se activa cuando el 50% de la sección está visible
    rootMargin: '0px 0px -50% 0px' // Ajuste para que se active en el centro
  });

  sections.forEach(section => {
    spyObserver.observe(section);
  });
  
  // Click en los puntos del Scroll-Spy
  spyDots.forEach(dot => {
    dot.addEventListener('click', function(e) {
      e.preventDefault();
      const targetId = this.getAttribute('href');
      const targetSection = document.querySelector(targetId);
      if (targetSection) {
        // Scroll suave
        targetSection.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

});
