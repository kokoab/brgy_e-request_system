@extends('layouts.app')

@section('title', 'Home - Boarding House Finder')

@section('content')
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #4A7C7E 0%, #5F9EA0 50%, #6B8E23 100%);" class="text-white py-24 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-64 h-64 rounded-full -translate-x-1/2 -translate-y-1/2" style="background-color: #D4A574;"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 rounded-full translate-x-1/2 translate-y-1/2" style="background-color: #D2691E;"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 text-center relative z-10">
        <h1 class="text-6xl font-bold mb-6 tracking-tight">Find Your Perfect Boarding House</h1>
        <p class="text-2xl mb-10 text-white/90 font-light">Discover comfortable and affordable boarding houses in Tacloban City</p>
        <div class="flex justify-center gap-4 flex-wrap">
            <a href="#browse" style="background-color: #D4A574; color: #2C3E50;" class="px-10 py-4 rounded-lg font-bold text-lg shadow-xl hover:opacity-90 transition-smooth">
                Browse Properties
            </a>
            <a href="/properties/create" style="background-color: white; color: #4A7C7E;" class="px-10 py-4 rounded-lg font-bold text-lg shadow-xl hover:opacity-90 transition-smooth">
                List Your Property
            </a>
        </div>
    </div>
</div>

<!-- Search Section -->
<div id="browse" class="max-w-7xl mx-auto py-12 px-4">
    <div class="bg-white p-8 rounded-xl shadow-lg mb-10" style="border: 2px solid rgba(74, 124, 126, 0.2);">
        <h2 style="color: #2C3E50;" class="text-3xl font-bold mb-6">Search Properties</h2>
        <form id="searchForm" class="grid grid-cols-1 md:grid-cols-4 gap-5">
            <div>
                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Search</label>
                <input type="text" id="search" name="search" placeholder="Search by name, location..." 
                       style="border: 2px solid rgba(74, 124, 126, 0.3);" 
                       class="w-full px-4 py-3 rounded-lg focus:outline-none transition-smooth" 
                       onfocus="this.style.borderColor='#4A7C7E'" onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
            </div>
            <div>
                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">City</label>
                <input type="text" id="city" name="city" placeholder="City" 
                       style="border: 2px solid rgba(74, 124, 126, 0.3);" 
                       class="w-full px-4 py-3 rounded-lg focus:outline-none transition-smooth"
                       onfocus="this.style.borderColor='#4A7C7E'" onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
            </div>
            <div>
                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Price Range</label>
                <div class="flex gap-2">
                    <input type="number" id="min_price" name="min_price" placeholder="Min" 
                           style="border: 2px solid rgba(74, 124, 126, 0.3);" 
                           class="w-full px-4 py-3 rounded-lg focus:outline-none transition-smooth"
                           onfocus="this.style.borderColor='#4A7C7E'" onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                    <input type="number" id="max_price" name="max_price" placeholder="Max" 
                           style="border: 2px solid rgba(74, 124, 126, 0.3);" 
                           class="w-full px-4 py-3 rounded-lg focus:outline-none transition-smooth"
                           onfocus="this.style.borderColor='#4A7C7E'" onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                </div>
            </div>
            <div>
                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Property Type</label>
                <select id="property_type" name="property_type" 
                        style="border: 2px solid rgba(74, 124, 126, 0.3);" 
                        class="w-full px-4 py-3 rounded-lg focus:outline-none transition-smooth"
                        onfocus="this.style.borderColor='#4A7C7E'" onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                    <option value="">All Types</option>
                    <option value="boarding_house">Boarding House</option>
                    <option value="apartment">Apartment</option>
                    <option value="dormitory">Dormitory</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="md:col-span-4 flex gap-3">
                <button type="submit" style="background-color: #4A7C7E; color: white;" class="px-8 py-3 rounded-lg font-semibold shadow-md hover:opacity-90 transition-smooth">
                    Search
                </button>
                <button type="button" onclick="resetFilters()" style="background-color: #2C3E50; color: white;" class="px-8 py-3 rounded-lg font-semibold shadow-md hover:opacity-90 transition-smooth">
                    Reset
                </button>
            </div>
        </form>
    </div>

    <!-- Map View -->
    <div class="bg-white p-8 rounded-xl shadow-lg mb-10" style="border: 2px solid rgba(74, 124, 126, 0.2);">
        <h2 style="color: #2C3E50;" class="text-3xl font-bold mb-6">Properties on Map</h2>
        <div class="mb-4 flex gap-2">
            <input type="text" id="mapSearch" placeholder="Search for a location (e.g., Tacloban City, Barangay 1)" 
                   style="border: 2px solid rgba(74, 124, 126, 0.3);" 
                   class="flex-1 px-4 py-3 rounded-lg focus:outline-none transition-smooth"
                   onfocus="this.style.borderColor='#4A7C7E'" onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
            <button onclick="searchMapLocation()" style="background-color: #4A7C7E; color: white;" class="px-6 py-3 rounded-lg font-semibold shadow-md hover:opacity-90 transition-smooth">
                Search
            </button>
        </div>
        <div id="map"></div>
    </div>

    <!-- Properties List -->
    <div class="mb-8">
        <h2 style="color: #2C3E50;" class="text-2xl font-bold mb-4">Available Properties</h2>
        <div id="propertiesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <p class="col-span-full text-center" style="color: #666;">Loading properties...</p>
        </div>
        <div id="pagination" class="mt-8"></div>
    </div>
</div>

<!-- Features Section -->
<div style="background-color: #F5E6D3;" class="py-12">
    <div class="max-w-7xl mx-auto px-4">
        <h2 style="color: #2C3E50;" class="text-3xl font-bold text-center mb-8">Why Choose Us?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-6 bg-white rounded-xl shadow-md" style="border: 2px solid rgba(212, 165, 116, 0.2);">
                <div class="text-5xl mb-4">🏠</div>
                <h3 style="color: #2C3E50;" class="text-2xl font-bold mb-3">Verified Properties</h3>
                <p style="color: rgba(44, 62, 80, 0.7);">All properties are verified and approved by our team</p>
            </div>
            <div class="text-center p-6 bg-white rounded-xl shadow-md" style="border: 2px solid rgba(74, 124, 126, 0.2);">
                <div class="text-5xl mb-4">📍</div>
                <h3 style="color: #2C3E50;" class="text-2xl font-bold mb-3">Easy Search</h3>
                <p style="color: rgba(44, 62, 80, 0.7);">Find properties near you with our interactive map</p>
            </div>
            <div class="text-center p-6 bg-white rounded-xl shadow-md" style="border: 2px solid rgba(210, 105, 30, 0.2);">
                <div class="text-5xl mb-4">⭐</div>
                <h3 style="color: #2C3E50;" class="text-2xl font-bold mb-3">Trusted Reviews</h3>
                <p style="color: rgba(44, 62, 80, 0.7);">Read reviews from real tenants before you decide</p>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map;
    let markers = [];

    // Initialize map - Tacloban City coordinates
    function initMap() {
        map = L.map('map').setView([11.2444, 125.0058], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
    }

    // Load properties
    async function loadProperties(page = 1) {
        const formData = new FormData(document.getElementById('searchForm'));
        const params = new URLSearchParams();
        
        for (const [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }
        params.append('page', page);

        try {
            const response = await fetch(`/api/properties?${params}`);
            const data = await response.json();
            
            displayProperties(data.data || data);
            displayPagination(data);
            updateMap(data.data || data);
        } catch (error) {
            console.error('Error loading properties:', error);
            document.getElementById('propertiesList').innerHTML = 
                '<p class="col-span-full text-center text-red-500">Error loading properties. Please try again.</p>';
        }
    }

    function displayProperties(properties) {
        const container = document.getElementById('propertiesList');
        container.innerHTML = '';

        if (!properties || properties.length === 0) {
            container.innerHTML = '<p class="col-span-full text-center" style="color: #666;">No properties found.</p>';
            return;
        }

        properties.forEach(property => {
            const card = createPropertyCard(property);
            container.appendChild(card);
        });
    }

    function createPropertyCard(property) {
        const div = document.createElement('div');
        div.className = 'bg-white rounded-xl shadow-lg overflow-hidden card-hover border-2 border-mcm-teal/10';
        
        const primaryImage = property.images && property.images.length > 0 
            ? `/storage/${property.images[0].image_path}` 
            : 'https://via.placeholder.com/400x300?text=No+Image';
        
        const avgRating = property.reviews && property.reviews.length > 0
            ? (property.reviews.reduce((sum, r) => sum + r.rating, 0) / property.reviews.length).toFixed(1)
            : '0.0';

        div.innerHTML = `
            <img src="${primaryImage}" alt="${property.title}" class="w-full h-56 object-cover" onerror="this.src='https://via.placeholder.com/400x300?text=No+Image'">
            <div class="p-6">
                <h3 style="color: #2C3E50;" class="text-2xl font-bold mb-2">${property.title}</h3>
                <p style="color: #4A7C7E;" class="text-sm mb-3 font-semibold">📍 ${property.city}</p>
                <p style="color: #D2691E;" class="text-3xl font-bold mb-3">₱${parseFloat(property.price).toLocaleString()}</p>
                <div class="flex items-center mb-3">
                    <span style="color: #D4A574;" class="text-xl">★</span>
                    <span style="color: #2C3E50;" class="ml-2 font-semibold">${avgRating} (${property.reviews?.length || 0} reviews)</span>
                </div>
                <p style="color: rgba(44, 62, 80, 0.7);" class="text-sm mb-5 line-clamp-2">${property.description}</p>
                <a href="/properties/${property.id}" style="background-color: #4A7C7E; color: white;" class="px-6 py-3 rounded-lg font-semibold shadow-md inline-block w-full text-center hover:opacity-90 transition-smooth">
                    View Details
                </a>
            </div>
        `;
        return div;
    }

    function displayPagination(data) {
        const container = document.getElementById('pagination');
        if (!data.links || data.links.length <= 3) {
            container.innerHTML = '';
            return;
        }

        let html = '<div class="flex justify-center gap-2">';
        data.links.forEach(link => {
            const active = link.active ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100';
            const disabled = link.url === null ? 'opacity-50 cursor-not-allowed' : '';
            html += `
                <button onclick="loadProperties(${link.label})" 
                        class="px-4 py-2 border rounded ${active} ${disabled}"
                        ${link.url === null ? 'disabled' : ''}>
                    ${link.label.replace('&laquo;', '«').replace('&raquo;', '»')}
                </button>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    function updateMap(properties) {
        // Clear existing markers
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];

        if (!properties || properties.length === 0) return;

        properties.forEach(property => {
            if (property.latitude && property.longitude) {
                const marker = L.marker([property.latitude, property.longitude])
                    .addTo(map)
                    .bindPopup(`
                        <div style="min-width: 150px;">
                            <strong>${property.title}</strong><br>
                            ${property.city}<br>
                            ₱${parseFloat(property.price).toLocaleString()}<br>
                            <a href="/properties/${property.id}" class="text-blue-600 hover:underline font-semibold mt-2 inline-block">View Details →</a>
                        </div>
                    `);
                
                // Make marker clickable to redirect to property details
                marker.on('click', function() {
                    window.location.href = `/properties/${property.id}`;
                });
                
                markers.push(marker);
            }
        });

        // Fit map to show all markers, but only if there are markers
        if (markers.length > 0) {
            const group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        } else {
            // If no markers, keep default view (Tacloban City)
            map.setView([11.2444, 125.0058], 13);
        }
    }

    function resetFilters() {
        document.getElementById('searchForm').reset();
        loadProperties(1);
    }

    // Search for location on map
    async function searchMapLocation() {
        const searchInput = document.getElementById('mapSearch');
        const query = searchInput.value.trim();
        
        if (!query) {
            alert('Please enter a location to search');
            return;
        }
        
        try {
            // Use Nominatim geocoding API
            const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1&countrycodes=ph`);
            const data = await response.json();
            
            if (data && data.length > 0) {
                const result = data[0];
                const lat = parseFloat(result.lat);
                const lng = parseFloat(result.lon);
                
                // Center map on searched location
                map.setView([lat, lng], 15);
                
                // Add a temporary marker for the searched location
                const searchMarker = L.marker([lat, lng], {icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34]
                })})
                    .addTo(map)
                    .bindPopup(`<strong>Searched: ${result.display_name}</strong>`)
                    .openPopup();
                
                // Remove search marker after 5 seconds
                setTimeout(() => {
                    map.removeLayer(searchMarker);
                }, 5000);
            } else {
                alert('Location not found. Please try a different search term.');
            }
        } catch (error) {
            console.error('Geocoding error:', error);
            alert('Error searching location. Please try again.');
        }
    }

    // Event listeners
    document.getElementById('searchForm').addEventListener('submit', (e) => {
        e.preventDefault();
        loadProperties(1);
    });

    // Allow Enter key to trigger map search
    document.addEventListener('DOMContentLoaded', () => {
        initMap();
        loadProperties(1);
        
        const mapSearchInput = document.getElementById('mapSearch');
        if (mapSearchInput) {
            mapSearchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchMapLocation();
                }
            });
        }
    });
</script>
@endsection
