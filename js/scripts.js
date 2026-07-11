document.addEventListener("DOMContentLoaded", () => {
    const body = document.body;
    const header = document.getElementById("site-header");
    const themeToggle = document.getElementById("theme-toggle");
    const mobileMenuToggle = document.getElementById("mobile-menu-toggle");
    const headerNav = document.getElementById("header-nav");
    const yearEl = document.getElementById("year");

    if (yearEl) {
        yearEl.textContent = String(new Date().getFullYear());
    }

    // Theme — cyberpunk defaults to dark
    const storedTheme = localStorage.getItem("theme");
    const initialTheme = storedTheme || "dark";
    body.setAttribute("data-theme", initialTheme);

    if (themeToggle) {
        themeToggle.addEventListener("click", () => {
            const next = body.getAttribute("data-theme") === "dark" ? "light" : "dark";
            body.setAttribute("data-theme", next);
            localStorage.setItem("theme", next);
        });
    }

    // Header scroll state
    const syncHeader = () => {
        if (!header) return;
        header.classList.toggle("is-scrolled", window.scrollY > 24);
    };
    syncHeader();
    window.addEventListener("scroll", syncHeader, { passive: true });

    // Mobile nav
    const closeMobileNav = () => {
        if (!mobileMenuToggle || !headerNav) return;
        mobileMenuToggle.classList.remove("active");
        mobileMenuToggle.setAttribute("aria-expanded", "false");
        headerNav.classList.remove("mobile-open");
    };

    if (mobileMenuToggle && headerNav) {
        mobileMenuToggle.addEventListener("click", (event) => {
            event.stopPropagation();
            const isOpen = headerNav.classList.toggle("mobile-open");
            mobileMenuToggle.classList.toggle("active", isOpen);
            mobileMenuToggle.setAttribute("aria-expanded", String(isOpen));
        });

        headerNav.querySelectorAll(".nav-link").forEach((link) => {
            link.addEventListener("click", closeMobileNav);
        });

        document.addEventListener("click", (event) => {
            if (
                !mobileMenuToggle.contains(event.target) &&
                !headerNav.contains(event.target)
            ) {
                closeMobileNav();
            }
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") closeMobileNav();
        });
    }

    // Smooth in-page anchors
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", (event) => {
            const id = anchor.getAttribute("href");
            if (!id || id === "#") return;
            const target = document.querySelector(id);
            if (!target) return;
            event.preventDefault();
            const offset = header ? header.offsetHeight + 12 : 80;
            const top = target.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top, behavior: "smooth" });
        });
    });

    // Reveal on scroll
    const revealEls = document.querySelectorAll(".reveal");
    if ("IntersectionObserver" in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("is-visible");
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.16, rootMargin: "0px 0px -40px 0px" }
        );
        revealEls.forEach((el) => observer.observe(el));
    } else {
        revealEls.forEach((el) => el.classList.add("is-visible"));
    }

    body.classList.add("loaded");
});
