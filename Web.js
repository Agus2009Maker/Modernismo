/* =============================================
   PLAYTIME LAUNCHER - Web.js v2.0
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

    /* ========= TEMA CLARO / OSCURO ========= */
    const themeToggleBtn = document.getElementById('theme-toggle');
    const htmlElement = document.documentElement;

    // Persistir tema en localStorage
    const savedTheme = localStorage.getItem('playtime-theme') || 'dark';
    htmlElement.setAttribute('data-theme', savedTheme);
    if (themeToggleBtn) {
        themeToggleBtn.innerHTML = savedTheme === 'dark' ? 'Modo Diurno ☀️' : 'Modo Nocturno 🌙';
    }

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const isDark = htmlElement.getAttribute('data-theme') === 'dark';
            const newTheme = isDark ? 'light' : 'dark';
            htmlElement.setAttribute('data-theme', newTheme);
            themeToggleBtn.innerHTML = isDark ? 'Modo Nocturno 🌙' : 'Modo Diurno ☀️';
            localStorage.setItem('playtime-theme', newTheme);
        });
    }

    /* ========= ANIMACIONES DE ENTRADA (IntersectionObserver) ========= */
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

    // Animar tarjetas al hacer scroll
    document.querySelectorAll('.chapter-card, .info-box, .guide-card, .step, .glass-panel').forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(25px)';
        el.style.transition = `opacity 0.5s ease ${i * 0.05}s, transform 0.5s ease ${i * 0.05}s, background 0.3s, border-color 0.3s, box-shadow 0.3s`;
        observer.observe(el);
    });

    /* ========= NAVBAR: RESALTAR LINK ACTIVO AL SCROLL ========= */
    const sections = document.querySelectorAll('section[id], header[id]');
    const navLinks = document.querySelectorAll('.navbar a[href^="#"]');

    if (sections.length && navLinks.length) {
        const highlightNav = () => {
            let current = '';
            sections.forEach(sec => {
                if (window.scrollY >= sec.offsetTop - 100) {
                    current = sec.getAttribute('id');
                }
            });
            navLinks.forEach(link => {
                link.style.color = '';
                link.style.textShadow = '';
                if (link.getAttribute('href') === `#${current}`) {
                    link.style.color = 'var(--accent)';
                    link.style.textShadow = 'var(--neon-main)';
                }
            });
        };
        window.addEventListener('scroll', highlightNav, { passive: true });
    }

    /* ========= EFECTO GLITCH en título principal ========= */
    const mainTitle = document.querySelector('h1.neon-text');
    if (mainTitle) {
        const originalText = mainTitle.textContent;
        const glitchChars = '!<>-_\\/[]{}—=+*^?#░▒▓';

        let glitchInterval = null;

        const triggerGlitch = () => {
            if (glitchInterval) return;
            let count = 0;
            glitchInterval = setInterval(() => {
                if (count >= 6) {
                    mainTitle.textContent = originalText;
                    clearInterval(glitchInterval);
                    glitchInterval = null;
                    return;
                }
                const arr = originalText.split('');
                const numGlitch = Math.floor(Math.random() * 4) + 1;
                for (let i = 0; i < numGlitch; i++) {
                    const idx = Math.floor(Math.random() * arr.length);
                    arr[idx] = glitchChars[Math.floor(Math.random() * glitchChars.length)];
                }
                mainTitle.textContent = arr.join('');
                count++;
            }, 60);
        };

        // Glitch al cargar
        setTimeout(triggerGlitch, 1200);
        // Glitch cada cierto tiempo
        setInterval(() => {
            if (Math.random() > 0.6) triggerGlitch();
        }, 6000);

        mainTitle.addEventListener('mouseenter', triggerGlitch);
    }

    /* ========= BOTONES COPIAR (Wine.html) ========= */
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const wrapper = btn.closest('.code-wrapper');
            const codeEl = wrapper ? wrapper.querySelector('code') : null;
            const textToCopy = codeEl ? codeEl.textContent.trim() : btn.dataset.copy;

            if (!textToCopy) return;

            navigator.clipboard.writeText(textToCopy).then(() => {
                const original = btn.textContent;
                btn.textContent = '✓ COPIADO';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.textContent = original;
                    btn.classList.remove('copied');
                }, 2000);
            }).catch(() => {
                // Fallback
                const textarea = document.createElement('textarea');
                textarea.value = textToCopy;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                btn.textContent = '✓ COPIADO';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.textContent = 'COPIAR';
                    btn.classList.remove('copied');
                }, 2000);
            });
        });
    });

    /* ========= NAVBAR: OCULTAR/MOSTRAR AL SCROLL ========= */
    let lastScrollY = window.scrollY;
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            const scrollY = window.scrollY;
            if (scrollY > 100 && scrollY > lastScrollY) {
                navbar.style.transform = 'translateY(-100%)';
            } else {
                navbar.style.transform = 'translateY(0)';
            }
            lastScrollY = scrollY;
        }, { passive: true });
        navbar.style.transition = 'transform 0.3s ease, box-shadow 0.3s';
    }

});
