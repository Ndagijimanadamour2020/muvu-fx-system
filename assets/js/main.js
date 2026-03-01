/**
 * MUVU FX – Main Application Script
 * Ensures consistent behavior across all pages.
 * Place this file in `assets/js/main.js` and include it after jQuery and Bootstrap in the footer.
 */

$(document).ready(function() {
    'use strict';

    /* ========== SIDEBAR TOGGLE ========== */
    window.toggleSidebar = function() {
        const sidebar = document.querySelector('.sidebar');
        if (!sidebar) return;

        if (window.innerWidth <= 768) {
            // Mobile: toggle mobile-visible class
            sidebar.classList.toggle('mobile-visible');
        } else {
            // Desktop: toggle collapsed class
            sidebar.classList.toggle('collapsed');
        }
    };

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.querySelector('.sidebar-toggle');
        if (window.innerWidth <= 768 && sidebar && toggleBtn) {
            if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                sidebar.classList.remove('mobile-visible');
            }
        }
    });

    // Handle window resize – ensure sidebar state resets on desktop
    window.addEventListener('resize', function() {
        const sidebar = document.querySelector('.sidebar');
        if (!sidebar) return;
        if (window.innerWidth > 768) {
            sidebar.classList.remove('mobile-visible');
        }
    });

    /* ========== BOOTSTRAP TOOLTIPS ========== */
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    /* ========== AUTO‑HIDE ALERTS ========== */
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    /* ========== UTILITY FUNCTIONS ========== */
    window.confirmDelete = function(message) {
        return confirm(message || 'Are you sure you want to delete this item?');
    };

    window.formatCurrency = function(amount) {
        return new Intl.NumberFormat('en-RW', {
            style: 'currency',
            currency: 'RWF',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    };

    window.formatDate = function(date) {
        return new Date(date).toLocaleDateString('en-RW', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    /* ========== SEARCH FUNCTIONALITY ========== */
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        // Target the table that contains the search input (usually the main table)
        var $table = $(this).closest('.card').find('table');
        if ($table.length === 0) {
            // Fallback: filter all tables on the page
            $table = $('table');
        }
        $table.find('tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    /* ========== PRINT BUTTON ========== */
    $('.print-btn').on('click', function(e) {
        e.preventDefault();
        window.print();
    });

    /* ========== EXPORT TO CSV (fallback) ========== */
    $('.export-btn').on('click', function(e) {
        e.preventDefault();
        var $table = $(this).closest('.card').find('table');
        if ($table.length === 0) return;

        var csv = [];
        var rows = $table[0].querySelectorAll('tr');

        for (var i = 0; i < rows.length; i++) {
            var row = [],
                cols = rows[i].querySelectorAll('td, th');
            for (var j = 0; j < cols.length; j++) {
                row.push(cols[j].innerText);
            }
            csv.push(row.join(','));
        }

        downloadCSV(csv.join('\n'), 'export.csv');
    });

    function downloadCSV(csv, filename) {
        var csvFile = new Blob([csv], { type: 'text/csv' });
        var downloadLink = document.createElement('a');
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }

    /* ========== KEYBOARD SHORTCUTS ========== */
    document.addEventListener('keydown', function(e) {
        // Ctrl + S – trigger first submit button
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            var submitBtn = document.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.click();
        }

        // Ctrl + F – focus search input
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            document.getElementById('searchInput')?.focus();
        }

        // Escape – close any open modal
        if (e.key === 'Escape') {
            var modals = document.querySelectorAll('.modal.show');
            modals.forEach(function(modal) {
                var modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) modalInstance.hide();
            });
        }
    });

    /* ========== MOBILE RESPONSIVE ADJUSTMENTS ========== */
    if (window.innerWidth <= 768) {
        $('.sidebar').addClass('collapsed');
        $('.main-content-right').css('margin-left', '0'); // ensure proper offset
    }

    /* ========== 3D CARD HOVER EFFECT ========== */
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            const rotateX = (y - centerY) / 10;
            const rotateY = (centerX - x) / 10;

            this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(10px)`;
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateZ(0)';
        });
    });

    /* ========== ANIMATED COUNTERS ========== */
    function animateCounter(element, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            element.innerText = Math.floor(progress * (end - start) + start);
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    document.querySelectorAll('.stat-value').forEach(counter => {
        const target = parseInt(counter.innerText.replace(/[^0-9]/g, ''));
        if (!isNaN(target) && target > 0) {
            animateCounter(counter, 0, target, 2000);
        }
    });
});