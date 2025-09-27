document.addEventListener('DOMContentLoaded', function() {

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    if (document.getElementById('destinations-container')) {
        loadDestinations();
    }

    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    }
});

async function loadDestinations() {
    console.log('loadDestinations called');
    try {
        console.log('Fetching destinations from api/destinations.php');
        const response = await fetch('api/destinations.php');
        console.log('Response received:', response);

        const json = await response.json();
        console.log('JSON parsed:', json);

        const destinations = json?.success && json?.data ? json.data : [];
        console.log('Destinations extracted:', destinations);

        const container = document.getElementById('destinations-container');
        console.log('Container found:', container);

        if (!container) {
            console.warn('Destinations container not found');
            return;
        }

        if (destinations.length === 0) {
            console.log('No destinations found, showing empty message');
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="alert alert-info">
                        Aucune destination disponible pour le moment. Revenez plus tard !
                    </div>
                </div>
            `;
            return;
        }

        console.log('Building HTML for', destinations.length, 'destinations');
        let html = '';
        destinations.forEach(destination => {
            html += `
                <div class="col-md-4 mb-4" data-aos="fade-up">
                    <div class="card destination-card h-100">
                        <div class="position-relative">
                            <img src="${destination.image_url || 'assets/images/v.png'}"
                                 class="card-img-top"
                                 alt="${destination.name}"
                                 style="height: 200px; object-fit: cover;">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">${destination.name}</h5>
                            <p class="card-text">
                                ${destination.description || 'Découvrez cette magnifique destination pour vos prochaines vacances.'}
                            </p>
                            <a href="booking.php?destination=${destination.id}" class="btn btn-primary">
                                Réserver maintenant
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        console.log('Destinations loaded successfully');
    } catch (error) {
        console.error('Error loading destinations:', error);
        const container = document.getElementById('destinations-container');
        if (container) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="alert alert-danger">
                        Une erreur est survenue lors du chargement des destinations. Veuillez réessayer plus tard.
                    </div>
                </div>
            `;
        }
    }
}

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    const reader = new FileReader();

    reader.onloadend = function() {
        preview.src = reader.result;
        preview.style.display = 'block';
    }

    if (file) {
        reader.readAsDataURL(file);
    } else {
        preview.src = '';
        preview.style.display = 'none';
    }
}

function formatCurrency(amount, currency = 'MAD') {
    return new Intl.NumberFormat('fr-MA', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2
    }).format(amount);
}

function setButtonLoading(button, isLoading) {
    if (isLoading) {
        button.disabled = true;
        button.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Traitement...
        `;
    } else {
        button.disabled = false;
        button.innerHTML = button.getAttribute('data-original-text');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-loading').forEach(button => {
        button.setAttribute('data-original-text', button.innerHTML);
        button.addEventListener('click', function() {
            setButtonLoading(this, true);
        });
    });
});

function submitForm(formId, successCallback, errorCallback) {
    const form = document.getElementById(formId);
    if (!form) return;

    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton ? submitButton.innerHTML : null;

    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Traitement...
        `;
    }

    fetch(form.action, {
        method: form.method,
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof successCallback === 'function') {
                successCallback(data);
            }
        } else {
            if (typeof errorCallback === 'function') {
                errorCallback(data);
            } else {
                showAlert('error', data.message || 'Une erreur est survenue');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof errorCallback === 'function') {
            errorCallback({ message: 'Une erreur réseau est survenue' });
        } else {
            showAlert('error', 'Une erreur réseau est survenue');
        }
    })
    .finally(() => {

        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    });
}

function showAlert(type, message, duration = 5000) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    let alertContainer = document.getElementById('alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alert-container';
        alertContainer.style.position = 'fixed';
        alertContainer.style.top = '20px';
        alertContainer.style.right = '20px';
        alertContainer.style.zIndex = '9999';
        alertContainer.style.maxWidth = '400px';
        document.body.appendChild(alertContainer);
    }

    const alertElement = document.createElement('div');
    alertElement.innerHTML = alertHtml;
    alertContainer.appendChild(alertElement);

    if (duration > 0) {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alertElement.querySelector('.alert'));
            bsAlert.close();

            setTimeout(() => {
                alertElement.remove();
            }, 150);
        }, duration);
    }
}

function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
}

function initPopovers() {
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
}

function debounce(func, wait, immediate) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

window.Vogie = {
    formatCurrency,
    showAlert,
    submitForm,
    initTooltips,
    initPopovers,
    debounce,
    throttle
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initTooltips();
        initPopovers();
        loadCitiesForSearchForm();
    });
} else {
    initTooltips();
    initPopovers();
    loadCitiesForSearchForm();
}

async function loadCitiesForSearchForm() {
    console.log('loadCitiesForSearchForm called');
    const fromSelect = document.getElementById('from-city');
    const toSelect = document.getElementById('to-city');
    console.log('Select elements found:', { fromSelect, toSelect });

    if (!fromSelect || !toSelect) {
        console.log('Select elements not found, returning');
        return;
    }

    try {
        console.log('Fetching cities from api/cities.php');
        const response = await fetch('api/cities.php');
        console.log('Cities response received:', response);

        const json = await response.json();
        console.log('Cities JSON parsed:', json);

        const cities = json?.success && json?.data ? json.data : [];
        console.log('Cities extracted:', cities);

        if (cities.length > 0) {
            console.log('Building city options for', cities.length, 'cities');
            const options = cities.map(c => `<option value="${c.name}" data-id="${c.id}">${c.name}</option>`).join('');
            fromSelect.innerHTML = `<option value="" selected disabled>Choisissez</option>` + options;
            toSelect.innerHTML = `<option value="" selected disabled>Choisissez</option>` + options;
            console.log('City options loaded successfully');
        } else {
            console.log('No cities found');
        }
    } catch (error) {
        console.error('Failed to load cities', error);
    }
}

async function loadAvailableRoutes(fromCityId, toCityId) {
    try {
        const response = await fetch(`api/routes.php?from=${fromCityId}&to=${toCityId}`);
        const json = await response.json();

        if (json?.success && json?.data) {
            const routes = json.data.filter(route =>
                route.from_city_id == fromCityId && route.to_city_id == toCityId
            );
            return routes;
        }
        return [];
    } catch (error) {
        console.error('Failed to load routes:', error);
        return [];
    }
}

async function loadRouteTimes(routeId) {
    try {
        const response = await fetch(`api/route_times.php?route_id=${routeId}`);
        const json = await response.json();

        if (json?.success && json?.data) {
            return json.data.filter(rt => rt.route_id == routeId);
        }
        return [];
    } catch (error) {
        console.error('Failed to load route times:', error);
        return [];
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('route-search-form');
    if (form) {

        const fromSelect = document.getElementById('from-city');
        const toSelect = document.getElementById('to-city');
        const passengersInput = document.getElementById('passengers');

        if (fromSelect && toSelect && passengersInput) {
            [fromSelect, toSelect, passengersInput].forEach(element => {
                element.addEventListener('change', updatePriceDisplay);
            });
        }
    }
});

async function updatePriceDisplay() {
    const fromSelect = document.getElementById('from-city');
    const toSelect = document.getElementById('to-city');
    const passengersInput = document.getElementById('passengers');
    const priceDisplay = document.getElementById('price-display');

    if (!fromSelect || !toSelect || !passengersInput || !priceDisplay) return;

    const fromId = fromSelect.options[fromSelect.selectedIndex]?.getAttribute('data-id');
    const toId = toSelect.options[toSelect.selectedIndex]?.getAttribute('data-id');
    const passengers = parseInt(passengersInput.value) || 1;

    if (fromId && toId && fromId !== toId) {
        try {

            priceDisplay.innerHTML = '<small class="text-muted">Calcul du prix...</small>';

            const response = await fetch('api/routes.php');
            const json = await response.json();

            if (json?.success && json?.data) {
                const route = json.data.find(r =>
                    r.from_city_id == fromId && r.to_city_id == toId
                );

                if (route) {
                    const totalPrice = route.price_per_person * passengers;
                    priceDisplay.innerHTML = `
                        <div class="text-success">
                            <strong>Prix: ${totalPrice} MAD</strong><br>
                            <small class="text-muted">(${route.price_per_person} MAD par personne)</small>
                        </div>
                    `;
                } else {
                    priceDisplay.innerHTML = '<small class="text-warning">Aucune route disponible</small>';
                }
            } else {
                priceDisplay.innerHTML = '<small class="text-muted">Prix non disponible</small>';
            }
        } catch (error) {
            console.error('Error loading price:', error);
            priceDisplay.innerHTML = '<small class="text-muted">Erreur de calcul</small>';
        }
    } else if (fromId === toId && fromId) {
        priceDisplay.innerHTML = '<small class="text-warning">Villes identiques</small>';
    } else {
        priceDisplay.innerHTML = '<small class="text-muted">Sélectionnez les villes</small>';
    }
}