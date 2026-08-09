(() => {
  const body = document.body;
  const header = document.getElementById("site-header");
  const themeToggle = document.getElementById("theme-toggle");
  const navToggle = document.getElementById("nav-toggle");
  const siteNav = document.getElementById("site-nav");
  const yearEl = document.getElementById("year");

  if (yearEl) {
    yearEl.textContent = String(new Date().getFullYear());
  }

  const storedTheme = localStorage.getItem("jpr-theme");
  const initialTheme = storedTheme || "light";
  body.setAttribute("data-theme", initialTheme);

  const syncThemeLabel = () => {
    if (!themeToggle) return;
    const isDark = body.getAttribute("data-theme") === "dark";
    themeToggle.setAttribute("aria-label", isDark ? "Switch to light theme" : "Switch to dark theme");
  };
  syncThemeLabel();

  themeToggle?.addEventListener("click", () => {
    const next = body.getAttribute("data-theme") === "dark" ? "light" : "dark";
    body.setAttribute("data-theme", next);
    localStorage.setItem("jpr-theme", next);
    syncThemeLabel();
  });

  const syncHeader = () => {
    header?.classList.toggle("is-scrolled", window.scrollY > 12);
  };
  syncHeader();
  window.addEventListener("scroll", syncHeader, { passive: true });

  const backToTop = document.getElementById("back-to-top");
  const syncBackToTop = () => {
    if (!backToTop) return;
    const show = window.scrollY > 480;
    backToTop.hidden = !show;
    backToTop.classList.toggle("is-visible", show);
  };
  syncBackToTop();
  window.addEventListener("scroll", syncBackToTop, { passive: true });
  backToTop?.addEventListener("click", () => {
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    window.scrollTo({ top: 0, behavior: reduceMotion ? "auto" : "smooth" });
  });

  const closeNav = () => {
    body.classList.remove("nav-open");
    navToggle?.setAttribute("aria-expanded", "false");
    siteNav?.querySelectorAll(".nav-item--dropdown.is-open").forEach((el) => {
      el.classList.remove("is-open");
      el.querySelector(".nav-link")?.setAttribute("aria-expanded", "false");
    });
  };

  navToggle?.addEventListener("click", (event) => {
    event.stopPropagation();
    const open = body.classList.toggle("nav-open");
    navToggle.setAttribute("aria-expanded", String(open));
  });

  siteNav?.querySelectorAll(".nav-item--dropdown").forEach((dropdown) => {
    const button = dropdown.querySelector(".nav-link--button");
    button?.addEventListener("click", (event) => {
      event.stopPropagation();
      const willOpen = !dropdown.classList.contains("is-open");
      siteNav.querySelectorAll(".nav-item--dropdown.is-open").forEach((other) => {
        if (other !== dropdown) {
          other.classList.remove("is-open");
          other.querySelector(".nav-link")?.setAttribute("aria-expanded", "false");
        }
      });
      dropdown.classList.toggle("is-open", willOpen);
      button.setAttribute("aria-expanded", String(willOpen));
    });
  });

  document.addEventListener("click", (event) => {
    if (!siteNav?.contains(event.target) && !navToggle?.contains(event.target)) {
      closeNav();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") closeNav();
  });

  // Reveal on scroll
  const reveals = document.querySelectorAll(".reveal");
  if ("IntersectionObserver" in window && reveals.length) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12 }
    );
    reveals.forEach((el) => io.observe(el));
  } else {
    reveals.forEach((el) => el.classList.add("is-visible"));
  }

  // Home featured projects carousel — 1 on mobile, 3 on desktop
  const carousel = document.querySelector("[data-carousel]");
  if (carousel) {
    const track = carousel.querySelector("[data-carousel-track]");
    const items = Array.from(carousel.querySelectorAll("[data-carousel-item]"));
    const dotsWrap = carousel.querySelector("[data-carousel-dots]");
    const prevBtn = carousel.querySelector("[data-carousel-prev]");
    const nextBtn = carousel.querySelector("[data-carousel-next]");
    const desktopCount = Number(carousel.getAttribute("data-carousel-desktop") || 3);
    const mobileCount = Number(carousel.getAttribute("data-carousel-mobile") || 1);
    const mq = window.matchMedia("(min-width: 720px)");
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    let index = 0;
    let timer = null;
    let dots = [];

    const itemsPerView = () => (mq.matches ? desktopCount : mobileCount);
    const pageCount = () => Math.max(1, Math.ceil(items.length / itemsPerView()));

    const syncDots = () => {
      if (!dotsWrap) return;
      const pages = pageCount();
      dotsWrap.innerHTML = "";
      dots = [];
      for (let i = 0; i < pages; i += 1) {
        const dot = document.createElement("button");
        dot.type = "button";
        dot.className = "project-carousel__dot";
        dot.setAttribute("data-carousel-dot", String(i));
        dot.setAttribute("aria-label", `Go to slide ${i + 1}`);
        dot.addEventListener("click", () => {
          goTo(i);
          start();
        });
        dotsWrap.appendChild(dot);
        dots.push(dot);
      }
    };

    const goTo = (nextIndex) => {
      if (!track || !items.length) return;
      const pages = pageCount();
      const ipv = itemsPerView();
      index = ((nextIndex % pages) + pages) % pages;
      const startItem = items[index * ipv];
      const offset = startItem ? startItem.offsetLeft : 0;
      track.style.transform = `translateX(-${offset}px)`;
      dots.forEach((dot, i) => {
        const active = i === index;
        dot.classList.toggle("is-active", active);
        if (active) dot.setAttribute("aria-current", "true");
        else dot.removeAttribute("aria-current");
      });
    };

    const stop = () => {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    };

    const start = () => {
      if (reduceMotion || pageCount() < 2) return;
      stop();
      timer = setInterval(() => goTo(index + 1), 5500);
    };

    const refresh = () => {
      syncDots();
      goTo(Math.min(index, pageCount() - 1));
      start();
    };

    prevBtn?.addEventListener("click", () => {
      goTo(index - 1);
      start();
    });
    nextBtn?.addEventListener("click", () => {
      goTo(index + 1);
      start();
    });

    carousel.addEventListener("mouseenter", stop);
    carousel.addEventListener("mouseleave", start);
    carousel.addEventListener("focusin", stop);
    carousel.addEventListener("focusout", start);

    window.addEventListener("resize", () => {
      goTo(index);
    }, { passive: true });

    mq.addEventListener("change", refresh);
    refresh();
  }

  // Catalog filters
  const catalog = document.querySelector("[data-catalog]");
  if (!catalog) return;

  const items = Array.from(catalog.querySelectorAll("[data-item]"));
  const searchInput = document.querySelector("[data-catalog-search]");
  const filterButtons = Array.from(document.querySelectorAll("[data-filter]"));
  const emptyState = document.querySelector("[data-catalog-empty]");
  const countEl = document.querySelector("[data-catalog-count]");
  const total = items.length;
  const noun = countEl?.getAttribute("data-noun") || "items";
  let activeFilter = "all";

  const updateCount = (visible) => {
    if (!countEl) return;
    if (visible === total) {
      countEl.textContent = `${total} ${noun}`;
    } else {
      countEl.textContent = `Showing ${visible} of ${total} ${noun}`;
    }
  };

  const applyFilters = () => {
    const query = (searchInput?.value || "").trim().toLowerCase();
    let visible = 0;

    items.forEach((item) => {
      const category = (item.getAttribute("data-category") || "").toLowerCase();
      const haystack = (item.getAttribute("data-search") || item.textContent || "").toLowerCase();
      const matchesFilter = activeFilter === "all" || category === activeFilter.toLowerCase();
      const matchesSearch = !query || haystack.includes(query);
      const show = matchesFilter && matchesSearch;
      item.classList.toggle("is-hidden", !show);
      if (show) visible += 1;
    });

    emptyState?.classList.toggle("is-hidden", visible > 0);
    updateCount(visible);
  };

  filterButtons.forEach((button) => {
    button.addEventListener("click", () => {
      activeFilter = button.getAttribute("data-filter") || "all";
      filterButtons.forEach((btn) => btn.classList.toggle("is-active", btn === button));
      applyFilters();
    });
  });

  searchInput?.addEventListener("input", applyFilters);
  applyFilters();
})();
