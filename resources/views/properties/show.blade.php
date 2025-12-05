@extends('layouts.app')

@section('title', 'Property Details')

@section('content')
<style>
    #editPropertyModal {
        z-index: 9999 !important;
    }
    #editPropertyModal > div {
        z-index: 10000 !important;
        position: relative;
    }
    #editPropertyMap {
        z-index: 1 !important;
        position: relative !important;
    }
    #editPropertyMap .leaflet-container {
        z-index: 1 !important;
        position: relative !important;
    }
    #editPropertyMap .leaflet-pane {
        z-index: 1 !important;
    }
    #editPropertyMap .leaflet-tile-pane {
        z-index: 1 !important;
    }
    #editPropertyMap .leaflet-overlay-pane {
        z-index: 2 !important;
    }
    #editPropertyMap .leaflet-marker-pane {
        z-index: 3 !important;
    }
    #editPropertyMap .leaflet-popup-pane {
        z-index: 4 !important;
    }
</style>
<div class="max-w-7xl mx-auto py-8 px-4">
    <div id="propertyDetails" class="bg-white rounded-lg shadow-md p-6">
        <p class="text-center text-gray-500">Loading property details...</p>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const propertyId = window.location.pathname.split('/').pop();
    let map;
    let property;
    let editRoomIndex = 0;
    
    // Get current user from localStorage (avoid duplicate declaration - use global from layout)
    if (typeof currentUser === 'undefined') {
        window.currentUser = null;
    }
    const userStr = localStorage.getItem('user');
    if (userStr) {
        try {
            window.currentUser = JSON.parse(userStr);
        } catch (e) {
            console.error('Error parsing user data:', e);
        }
    }

    async function loadProperty() {
        try {
            const token = localStorage.getItem('auth_token');
            const headers = {
                'Content-Type': 'application/json',
            };
            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }

            const response = await fetch(`/api/properties/${propertyId}`, { headers });
            
            if (!response.ok) {
                if (response.status === 404) {
                    document.getElementById('propertyDetails').innerHTML = 
                        '<div class="text-center py-12"><p class="text-red-500 text-xl mb-4">Property not found</p><a href="/" class="text-blue-500 hover:underline">Go back to home</a></div>';
                    return;
                }
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || 'Failed to load property');
            }
            
            property = await response.json();
            
            if (!property || !property.id) {
                throw new Error('Invalid property data received');
            }
            
            // Debug: Log property data
            console.log('Property data:', property);
            console.log('Is owner:', property.is_owner);
            console.log('All bookings:', property.all_bookings);
            if (property.all_bookings && property.all_bookings.length > 0) {
                console.log('First booking:', property.all_bookings[0]);
                console.log('Pending bookings:', property.all_bookings.filter(b => b.status === 'pending'));
            }
            
            // Update currentUser if property has current_user info
            if (property.current_user) {
                window.currentUser = property.current_user;
                // Update navbar
                if (typeof checkAuth === 'function') {
                    checkAuth();
                }
            }
            
            displayProperty(property);
            if (property.latitude && property.longitude) {
                initMap();
            }
        } catch (error) {
            console.error('Error loading property:', error);
            document.getElementById('propertyDetails').innerHTML = 
                `<div class="text-center py-12">
                    <p class="text-red-500 text-xl mb-4">Error loading property details: ${error.message}</p>
                    <a href="/" class="text-blue-500 hover:underline">Go back to home</a>
                </div>`;
        }
    }

    function displayProperty(property) {
        const container = document.getElementById('propertyDetails');
        
        const images = property.images && property.images.length > 0
            ? property.images.map(img => `<img src="/storage/${img.image_path}" alt="${property.title}" class="w-full h-64 object-cover rounded">`).join('')
            : '<div class="w-full h-64 bg-gray-200 rounded flex items-center justify-center">No images</div>';

        const amenities = property.amenities && property.amenities.length > 0
            ? property.amenities.map(a => `<span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">${a}</span>`).join(' ')
            : 'No amenities listed';

        const avgRating = property.average_rating ? parseFloat(property.average_rating) : 0;
        const reviews = property.reviews || [];
        const avgRatingDisplay = isNaN(avgRating) ? '0.0' : avgRating.toFixed(1);
        
        // Check if current user is the property owner
        const isOwner = property.is_owner !== undefined ? property.is_owner : (property.user && property.user.id && window.currentUser && window.currentUser.id == property.user.id);
        
        // Debug logging
        console.log('Display Property - isOwner:', isOwner);
        console.log('Display Property - property.is_owner:', property.is_owner);
        console.log('Display Property - currentUser:', window.currentUser);
        console.log('Display Property - property.user:', property.user);
        console.log('Display Property - all_bookings:', property.all_bookings);
        console.log('Display Property - all_bookings length:', property.all_bookings ? property.all_bookings.length : 0);

        container.innerHTML = `
            <div class="mb-6">
                <h1 class="text-3xl font-bold mb-2">${property.title}</h1>
                <p class="text-gray-600">${property.address}, ${property.city}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        ${images}
                    </div>
                </div>
                <div>
                    <div class="mb-4">
                        <p class="text-3xl font-bold text-blue-600 mb-2">₱${parseFloat(property.price).toLocaleString()}</p>
                        <div class="flex items-center mb-4">
                            <span class="text-yellow-500 text-xl">★</span>
                            <span class="ml-2 text-lg">${avgRatingDisplay} (${reviews.length} reviews)</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h3 class="font-bold mb-2">Property Details</h3>
                        <div class="grid grid-cols-2 gap-4">
                            ${property.bedrooms ? `<div><strong>Bedrooms:</strong> ${property.bedrooms}</div>` : ''}
                            ${property.bathrooms ? `<div><strong>Bathrooms:</strong> ${property.bathrooms}</div>` : ''}
                            ${property.capacity ? `<div><strong>Capacity:</strong> ${property.capacity}</div>` : ''}
                            <div><strong>Type:</strong> ${property.property_type.replace('_', ' ')}</div>
                        </div>
                    </div>

                    ${property.rooms && property.rooms.length > 0 ? `
                    <div class="mb-4">
                        <h3 class="font-bold mb-2">Rooms</h3>
                        <div class="space-y-2">
                            ${property.rooms.map(room => {
                                const occupancy = room.occupancy_count || 0;
                                const remaining = room.remaining_capacity !== undefined ? room.remaining_capacity : (room.capacity - occupancy);
                                const status = room.availability_status || (room.is_available && remaining > 0 ? 'available' : (remaining === 0 ? 'occupied' : 'unavailable'));
                                const statusColors = {
                                    'available': { bg: 'rgba(74, 124, 126, 0.1)', text: '#4A7C7E', label: 'Available' },
                                    'partially_occupied': { bg: 'rgba(212, 165, 116, 0.1)', text: '#D4A574', label: 'Partially Occupied' },
                                    'occupied': { bg: 'rgba(210, 105, 30, 0.1)', text: '#D2691E', label: 'Occupied' },
                                    'unavailable': { bg: 'rgba(44, 62, 80, 0.1)', text: '#2C3E50', label: 'Unavailable' }
                                };
                                const statusInfo = statusColors[status] || statusColors['available'];
                                
                                return `
                                <div class="p-3 border-2 rounded-lg" style="border-color: ${statusInfo.text}40; background-color: ${statusInfo.bg};">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <strong style="color: #2C3E50;">${room.name}</strong>
                                                <span class="px-2 py-1 rounded text-xs font-semibold" style="background-color: ${statusInfo.text}20; color: ${statusInfo.text};">
                                                    ${statusInfo.label}
                                                </span>
                                            </div>
                                            <p style="color: #D2691E;" class="font-semibold">₱${parseFloat(room.price).toLocaleString()}</p>
                                            <p style="color: rgba(44, 62, 80, 0.7);" class="text-sm">
                                                Capacity: ${room.capacity} person${room.capacity > 1 ? 's' : ''} | 
                                                Occupied: ${occupancy} | 
                                                Remaining: ${remaining}
                                            </p>
                                            ${room.description ? `<p style="color: rgba(44, 62, 80, 0.7);" class="text-sm mt-1">${room.description}</p>` : ''}
                                            
                                            ${isOwner && room.all_bookings && room.all_bookings.length > 0 ? `
                                            <div class="mt-3 pt-3 border-t" style="border-color: rgba(74, 124, 126, 0.2);">
                                                <p style="color: #2C3E50;" class="text-xs font-semibold mb-2">Occupants:</p>
                                                <div class="space-y-1">
                                                    ${room.all_bookings.filter(b => b.status === 'approved').map(booking => `
                                                        <div class="text-xs" style="color: rgba(44, 62, 80, 0.7);">
                                                            • ${booking.user ? booking.user.name : booking.name} ${booking.user ? `(${booking.user.email})` : `(${booking.email})`}
                                                        </div>
                                                    `).join('')}
                                                </div>
                                            </div>
                                            ` : ''}
                                        </div>
                                        ${isOwner ? `
                                        <div class="ml-4">
                                            <button onclick="toggleRoomAvailability(${room.id}, ${room.is_available ? 'false' : 'true'})" 
                                                    style="background-color: ${room.is_available ? '#D2691E' : '#4A7C7E'}; color: white;" 
                                                    class="px-3 py-1 rounded text-xs font-semibold hover:opacity-90 transition-smooth">
                                                ${room.is_available ? 'Mark Unavailable' : 'Mark Available'}
                                            </button>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            `;
                            }).join('')}
                        </div>
                    </div>
                    ` : ''}
                    
                    ${isOwner ? `
                    <!-- Bookings Management Section (Owner Only) -->
                    <div class="mb-6">
                        <h2 style="color: #2C3E50;" class="text-2xl font-bold mb-4">Booking Management</h2>
                        
                        <!-- Income Calculation -->
                        ${property.rooms && property.rooms.length > 0 ? `
                        <div class="mb-6 p-4 rounded-xl border-2" style="border-color: rgba(74, 124, 126, 0.3); background-color: rgba(74, 124, 126, 0.05);">
                            <div class="flex justify-between items-center mb-4">
                                <h3 style="color: #2C3E50;" class="text-xl font-bold">Income Calculation</h3>
                                <button onclick="openHistoryIncomeModal()" 
                                        style="background-color: #4A7C7E; color: white;" 
                                        class="px-4 py-2 rounded-lg font-semibold hover:opacity-90 transition-smooth shadow-md">
                                    History Income
                                </button>
                            </div>
                            <div class="space-y-3">
                                ${property.rooms.map(room => {
                                    const potentialIncome = parseFloat(room.price) * parseInt(room.capacity);
                                    const occupancy = room.occupancy_count || 0;
                                    const paidCount = room.paid_bookings_count || 0;
                                    const actualIncome = parseFloat(room.price) * paidCount;
                                    
                                    // Get approved bookings for this room
                                    const roomBookings = property.all_bookings ? property.all_bookings.filter(b => b.room_id === room.id && b.status === 'approved') : [];
                                    
                                    return `
                                    <div class="p-3 rounded-lg border" style="border-color: rgba(74, 124, 126, 0.2); background-color: rgba(74, 124, 126, 0.1);">
                                        <div class="flex justify-between items-center mb-2">
                                            <strong style="color: #2C3E50;">${room.name}</strong>
                                            <span style="color: #4A7C7E;" class="font-semibold">₱${parseFloat(room.price).toLocaleString()}/person</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4 text-sm mb-3" style="color: rgba(44, 62, 80, 0.7);">
                                            <div>
                                                <p><strong>Capacity:</strong> ${room.capacity} person${room.capacity > 1 ? 's' : ''}</p>
                                                <p><strong>Occupied:</strong> ${occupancy} person${occupancy !== 1 ? 's' : ''}</p>
                                                <p><strong>Paid:</strong> ${paidCount} person${paidCount !== 1 ? 's' : ''}</p>
                                            </div>
                                            <div>
                                                <p><strong>Potential Income:</strong> <span style="color: #4A7C7E; font-weight: bold;">₱${potentialIncome.toLocaleString()}</span></p>
                                                <p><strong>Actual Income (Paid):</strong> <span style="color: #D2691E; font-weight: bold;">₱${actualIncome.toLocaleString()}</span></p>
                                            </div>
                                        </div>
                                        ${roomBookings.length > 0 ? `
                                        <div class="mt-3 pt-3 border-t" style="border-color: rgba(74, 124, 126, 0.2);">
                                            <p style="color: #2C3E50;" class="text-xs font-semibold mb-2">Bookings:</p>
                                            <div class="space-y-2">
                                                ${roomBookings.map(booking => `
                                                    <div class="flex justify-between items-center p-2 rounded" style="background-color: rgba(44, 62, 80, 0.05);">
                                                        <div class="flex-1">
                                                            <p class="text-xs" style="color: rgba(44, 62, 80, 0.7);">
                                                                ${booking.user ? booking.user.name : booking.name}
                                                            </p>
                                                        </div>
                                                        <button onclick="toggleBookingPayment(${booking.id}, ${booking.is_paid ? 'false' : 'true'})" 
                                                                style="background-color: ${booking.is_paid ? '#4A7C7E' : '#D4A574'}; color: white;" 
                                                                class="px-3 py-1 rounded text-xs font-semibold hover:opacity-90 transition-smooth">
                                                            ${booking.is_paid ? '✓ Paid' : 'Mark Paid'}
                                                        </button>
                                                    </div>
                                                `).join('')}
                                            </div>
                                        </div>
                                        ` : ''}
                                    </div>
                                `;
                                }).join('')}
                                <div class="mt-4 p-4 rounded-lg border-2" style="border-color: #4A7C7E; background-color: rgba(74, 124, 126, 0.1);">
                                    <div class="flex justify-between items-center">
                                        <strong style="color: #2C3E50;" class="text-lg">Total Potential Income:</strong>
                                        <span style="color: #4A7C7E; font-weight: bold; font-size: 1.25rem;">
                                            ₱${property.rooms.reduce((total, room) => total + (parseFloat(room.price) * parseInt(room.capacity)), 0).toLocaleString()}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center mt-2">
                                        <strong style="color: #2C3E50;" class="text-lg">Total Actual Income (Paid):</strong>
                                        <span style="color: #D2691E; font-weight: bold; font-size: 1.25rem;">
                                            ₱${property.rooms.reduce((total, room) => total + (parseFloat(room.price) * (room.paid_bookings_count || 0)), 0).toLocaleString()}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ` : ''}
                        
                        ${property.all_bookings && property.all_bookings.length > 0 ? `
                        <!-- Pending Bookings Button -->
                        ${property.all_bookings.filter(b => b.status === 'pending').length > 0 ? `
                        <div class="mb-4">
                            <button onclick="openPendingBookingsModal()" 
                                    style="background-color: #D4A574; color: white;" 
                                    class="px-6 py-3 rounded-lg font-semibold hover:opacity-90 transition-smooth shadow-md flex items-center gap-2">
                                <span>Pending Bookings</span>
                                <span class="px-3 py-1 rounded-full text-sm" style="background-color: rgba(255, 255, 255, 0.3);">
                                    ${property.all_bookings.filter(b => b.status === 'pending').length}
                                </span>
                            </button>
                        </div>
                        ` : ''}
                        
                        <!-- All Bookings -->
                        <div class="p-4 rounded-xl border-2" style="border-color: rgba(74, 124, 126, 0.2); background-color: rgba(74, 124, 126, 0.05);">
                            <h3 style="color: #2C3E50;" class="text-xl font-bold mb-4">All Bookings</h3>
                            <div class="space-y-3">
                                ${property.all_bookings.map(booking => {
                                    const statusColors = {
                                        'pending': { bg: 'rgba(212, 165, 116, 0.1)', text: '#D4A574' },
                                        'approved': { bg: 'rgba(74, 124, 126, 0.1)', text: '#4A7C7E' },
                                        'rejected': { bg: 'rgba(210, 105, 30, 0.1)', text: '#D2691E' },
                                        'completed': { bg: 'rgba(44, 62, 80, 0.1)', text: '#2C3E50' }
                                    };
                                    const statusInfo = statusColors[booking.status] || statusColors['pending'];
                                    const bookingDate = new Date(booking.created_at).toLocaleDateString();
                                    
                                    return `
                                    <div class="p-4 rounded-lg border" style="border-color: ${statusInfo.text}40; background-color: ${statusInfo.bg};">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <strong style="color: #2C3E50;" class="text-lg">${booking.user ? booking.user.name : booking.name}</strong>
                                                    <span class="px-3 py-1 rounded text-sm font-semibold" style="background-color: ${statusInfo.text}20; color: ${statusInfo.text};">
                                                        ${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}
                                                    </span>
                                                    ${booking.room ? `<span style="color: #4A7C7E;" class="text-sm font-semibold">• ${booking.room.name}</span>` : ''}
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm" style="color: rgba(44, 62, 80, 0.7);">
                                                    <p><strong>Email:</strong> ${booking.user ? booking.user.email : booking.email}</p>
                                                    <p><strong>Phone:</strong> ${booking.user ? (booking.user.phone || 'N/A') : booking.phone}</p>
                                                    ${booking.check_in_date ? `<p><strong>Check-in:</strong> ${booking.check_in_date}</p>` : ''}
                                                    ${booking.check_out_date ? `<p><strong>Check-out:</strong> ${booking.check_out_date}</p>` : ''}
                                                </div>
                                                ${booking.message ? `<p style="color: rgba(44, 62, 80, 0.7);" class="text-sm mt-2 italic">"${booking.message}"</p>` : ''}
                                                <p style="color: rgba(44, 62, 80, 0.5);" class="text-xs mt-2">Booked on: ${bookingDate}</p>
                                            </div>
                                            ${booking.status === 'pending' ? `
                                            <div class="ml-4 flex flex-col gap-2">
                                                <button onclick="updateBookingStatus(${booking.id}, 'approved')" 
                                                        style="background-color: #4A7C7E; color: white;" 
                                                        class="px-4 py-2 rounded-lg font-semibold hover:opacity-90 transition-smooth shadow-md">
                                                    Approve
                                                </button>
                                                <button onclick="updateBookingStatus(${booking.id}, 'rejected')" 
                                                        style="background-color: #D2691E; color: white;" 
                                                        class="px-4 py-2 rounded-lg font-semibold hover:opacity-90 transition-smooth shadow-md">
                                                    Reject
                                                </button>
                                            </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                `;
                                }).join('')}
                            </div>
                        </div>
                        ` : `
                        <div class="p-6 rounded-xl border-2 text-center" style="border-color: rgba(74, 124, 126, 0.2); background-color: rgba(74, 124, 126, 0.05);">
                            <p style="color: rgba(44, 62, 80, 0.7);" class="text-lg font-semibold">No bookings yet</p>
                            <p style="color: rgba(44, 62, 80, 0.5);" class="text-sm mt-2">When users book your property, they will appear here for approval.</p>
                        </div>
                        `}
                    </div>
                    ` : ''}

                    <div class="mb-4">
                        <h3 class="font-bold mb-2">Amenities</h3>
                        <div class="flex flex-wrap gap-2">
                            ${amenities}
                        </div>
                    </div>

                    <div class="mb-4">
                        <h3 class="font-bold mb-2">Contact Information</h3>
                        ${property.contact_phone ? `<p><strong>Phone:</strong> ${property.contact_phone}</p>` : ''}
                        ${property.contact_email ? `<p><strong>Email:</strong> ${property.contact_email}</p>` : ''}
                    </div>

                    <div class="flex gap-2">
                        ${isOwner ? `
                        <button onclick="openEditPropertyModal()" style="background-color: #D2691E; color: white;" class="px-6 py-2 rounded-lg font-semibold shadow-md hover:opacity-90 transition-smooth">
                            Edit Property
                        </button>
                        ` : ''}
                        <button onclick="toggleFavorite()" id="favoriteBtn" style="background-color: #D4A574; color: #2C3E50;" class="px-4 py-2 rounded-lg font-semibold hover:opacity-90 transition-smooth">
                            ${property.is_favorited ? '★ Remove from Favorites' : '☆ Add to Favorites'}
                        </button>
                        ${!isOwner ? `
                        <button onclick="showBookingForm()" style="background-color: #4A7C7E; color: white;" class="px-6 py-2 rounded-lg font-semibold shadow-md hover:opacity-90 transition-smooth">
                            Book Now
                        </button>
                        ` : ''}
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <h2 class="text-2xl font-bold mb-4">Description</h2>
                <p class="text-gray-700">${property.description}</p>
            </div>

            ${property.latitude && property.longitude ? '<div id="propertyMap" class="mb-6" style="height: 400px;"></div>' : ''}

            <div class="mb-6">
                <h2 class="text-2xl font-bold mb-4">Reviews</h2>
                <div id="reviewsList">
                    ${reviews.length > 0 
                        ? reviews.map(r => `
                            <div class="border-b pb-4 mb-4">
                                <div class="flex items-center mb-2">
                                    <strong>${r.user.name}</strong>
                                    <span class="ml-2 text-yellow-500">${'★'.repeat(r.rating)}</span>
                                </div>
                                ${r.comment ? `<p class="text-gray-700">${r.comment}</p>` : ''}
                            </div>
                        `).join('')
                        : '<p class="text-gray-500">No reviews yet.</p>'
                    }
                </div>
                ${localStorage.getItem('auth_token') ? `
                    <button onclick="showReviewForm()" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Add Review
                    </button>
                ` : ''}
            </div>
        `;
    }

    function initMap() {
        if (!property.latitude || !property.longitude) return;
        
        // Use unique ID for property map
        const mapElement = document.getElementById('propertyMap');
        if (!mapElement) {
            return;
        }
        
        // Remove any existing map instance
        if (map) {
            map.remove();
        }
        
        map = L.map('propertyMap').setView([property.latitude, property.longitude], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        L.marker([property.latitude, property.longitude])
            .addTo(map)
            .bindPopup(`<strong>${property.title}</strong><br>${property.address}`);
    }

    async function toggleFavorite() {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            alert('Please login to add favorites');
            return;
        }

        try {
            const response = await fetch(`/api/properties/${propertyId}/favorite`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                }
            });
            const data = await response.json();
            property.is_favorited = data.is_favorited;
            document.getElementById('favoriteBtn').innerHTML = 
                data.is_favorited ? '★ Remove from Favorites' : '☆ Add to Favorites';
        } catch (error) {
            console.error('Error toggling favorite:', error);
        }
    }

    async function showBookingForm() {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            alert('Please login to book');
            window.location.href = '/login';
            return;
        }

        // Get user data from localStorage or API
        let userData = null;
        const userStr = localStorage.getItem('user');
        if (userStr) {
            try {
                userData = JSON.parse(userStr);
            } catch (e) {
                console.error('Error parsing user data:', e);
            }
        }

        // If user data not in localStorage, fetch from API
        if (!userData) {
            try {
                const response = await fetch('/api/user', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                    }
                });
                if (response.ok) {
                    userData = await response.json();
                    localStorage.setItem('user', JSON.stringify(userData));
                }
            } catch (error) {
                console.error('Error fetching user data:', error);
            }
        }

        if (!userData) {
            alert('Error loading user data. Please try again.');
            return;
        }

        // Show modal
        showBookingModal(userData);
    }

    function showBookingModal(userData) {
        // Close any existing modal first
        closeBookingModal();
        
        const rooms = property.rooms || [];
        const hasRooms = rooms.length > 0;
        
        const modal = document.createElement('div');
        modal.id = 'bookingModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.style.zIndex = '9999';
        modal.onclick = function(e) {
            if (e.target === modal) {
                closeBookingModal();
            }
        };
        
        // Build room selection HTML
        let roomSelectionHTML = '';
        if (hasRooms) {
            const availableRooms = rooms.filter(r => {
                const remaining = r.remaining_capacity !== undefined ? r.remaining_capacity : r.capacity;
                return r.is_available && remaining > 0;
            });
            
            roomSelectionHTML = `
                <div class="mb-6">
                    <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Select Room *</label>
                    <select id="bookingRoomId" required
                            class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                            style="border-color: rgba(74, 124, 126, 0.3);"
                            onfocus="this.style.borderColor='#4A7C7E'" 
                            onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'"
                            onchange="updateRoomPrice()">
                        <option value="">Choose a room...</option>
                        ${availableRooms.map(room => {
                            const occupancy = room.occupancy_count || 0;
                            const remaining = room.remaining_capacity !== undefined ? room.remaining_capacity : (room.capacity - occupancy);
                            return `
                            <option value="${room.id}" data-price="${room.price}" data-remaining="${remaining}">
                                ${room.name} - ₱${parseFloat(room.price).toLocaleString()} (${remaining} spot${remaining > 1 ? 's' : ''} available${occupancy > 0 ? `, ${occupancy} occupied` : ''})
                            </option>
                        `;
                        }).join('')}
                    </select>
                    <div id="roomDescription" class="mt-2 text-sm" style="color: rgba(44, 62, 80, 0.7);"></div>
                    <div id="roomCapacityInfo" class="mt-2 text-sm font-semibold" style="color: #4A7C7E;"></div>
                </div>
            `;
        }
        
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-xl p-8 max-w-md w-full mx-4" style="max-height: 90vh; overflow-y: auto; position: relative; z-index: 10000;">
                <div class="flex justify-between items-center mb-6">
                    <h2 style="color: #2C3E50;" class="text-2xl font-bold">Book Property</h2>
                    <button onclick="closeBookingModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>
                
                ${roomSelectionHTML}
                
                <div class="mb-6">
                    <h3 style="color: #2C3E50;" class="font-semibold mb-4">Your Information</h3>
                    <div class="space-y-3">
                        <div>
                            <label style="color: #2C3E50;" class="block text-sm font-semibold mb-1">Name</label>
                            <input type="text" id="bookingName" value="${userData.name || ''}" 
                                   class="w-full px-4 py-2 border-2 rounded-lg bg-gray-50" 
                                   style="border-color: rgba(74, 124, 126, 0.3); color: #666;" readonly>
                        </div>
                        <div>
                            <label style="color: #2C3E50;" class="block text-sm font-semibold mb-1">Email</label>
                            <input type="email" id="bookingEmail" value="${userData.email || ''}" 
                                   class="w-full px-4 py-2 border-2 rounded-lg bg-gray-50" 
                                   style="border-color: rgba(74, 124, 126, 0.3); color: #666;" readonly>
                        </div>
                        <div>
                            <label style="color: #2C3E50;" class="block text-sm font-semibold mb-1">Phone</label>
                            <input type="text" id="bookingPhone" value="${userData.phone || ''}" 
                                   class="w-full px-4 py-2 border-2 rounded-lg bg-gray-50" 
                                   style="border-color: rgba(74, 124, 126, 0.3); color: #666;" readonly>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Message to Property Owner (Optional)</label>
                    <textarea id="bookingMessage" rows="4" 
                              placeholder="Enter your message or any special requests..."
                              class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                              style="border-color: rgba(74, 124, 126, 0.3);"
                              onfocus="this.style.borderColor='#4A7C7E'" 
                              onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'"></textarea>
                </div>

                <div class="mb-4">
                    <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Check-in Date (Optional)</label>
                    <input type="date" id="bookingCheckIn" 
                           class="w-full px-4 py-2 border-2 rounded-lg focus:outline-none transition-smooth"
                           style="border-color: rgba(74, 124, 126, 0.3);"
                           onfocus="this.style.borderColor='#4A7C7E'" 
                           onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                </div>

                <div class="mb-6">
                    <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Check-out Date (Optional)</label>
                    <input type="date" id="bookingCheckOut" 
                           class="w-full px-4 py-2 border-2 rounded-lg focus:outline-none transition-smooth"
                           style="border-color: rgba(74, 124, 126, 0.3);"
                           onfocus="this.style.borderColor='#4A7C7E'" 
                           onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                </div>

                <div id="bookingError" class="hidden mb-4 p-3 rounded-lg" style="background-color: rgba(210, 105, 30, 0.1); color: #D2691E;"></div>

                <div class="flex gap-3">
                    <button onclick="submitBooking()" 
                            style="background-color: #4A7C7E; color: white;" 
                            class="flex-1 px-6 py-3 rounded-lg font-semibold shadow-md hover:opacity-90 transition-smooth">
                        Submit Booking
                    </button>
                    <button onclick="closeBookingModal()" 
                            style="background-color: #2C3E50; color: white;" 
                            class="px-6 py-3 rounded-lg font-semibold shadow-md hover:opacity-90 transition-smooth">
                        Cancel
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    function closeBookingModal() {
        const modal = document.getElementById('bookingModal');
        if (modal) {
            modal.remove();
        }
    }

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBookingModal();
            closePendingBookingsModal();
            closeHistoryIncomeModal();
            closeEditPropertyModal();
        }
    });

    // Open History Income Modal
    function openHistoryIncomeModal() {
        if (!property || !property.all_bookings) {
            alert('No bookings data available');
            return;
        }

        // Get all paid bookings
        const paidBookings = property.all_bookings.filter(b => b.status === 'approved' && b.is_paid);
        
        // Calculate monthly income
        const monthlyIncome = {};
        paidBookings.forEach(booking => {
            const bookingDate = new Date(booking.created_at || booking.updated_at);
            const monthKey = bookingDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
            const yearMonth = bookingDate.toLocaleDateString('en-US', { year: 'numeric', month: '2-digit' });
            
            if (!monthlyIncome[yearMonth]) {
                monthlyIncome[yearMonth] = {
                    label: monthKey,
                    total: 0,
                    bookings: []
                };
            }
            
            const room = property.rooms.find(r => r.id === booking.room_id);
            const income = room ? parseFloat(room.price) : 0;
            monthlyIncome[yearMonth].total += income;
            monthlyIncome[yearMonth].bookings.push({
                ...booking,
                income: income,
                roomName: room ? room.name : 'N/A'
            });
        });

        // Calculate yearly income
        const yearlyIncome = {};
        paidBookings.forEach(booking => {
            const bookingDate = new Date(booking.created_at || booking.updated_at);
            const year = bookingDate.getFullYear().toString();
            
            if (!yearlyIncome[year]) {
                yearlyIncome[year] = {
                    total: 0,
                    bookings: []
                };
            }
            
            const room = property.rooms.find(r => r.id === booking.room_id);
            const income = room ? parseFloat(room.price) : 0;
            yearlyIncome[year].total += income;
            yearlyIncome[year].bookings.push({
                ...booking,
                income: income,
                roomName: room ? room.name : 'N/A'
            });
        });

        // Sort months (newest first)
        const sortedMonths = Object.keys(monthlyIncome).sort((a, b) => b.localeCompare(a));
        // Sort years (newest first)
        const sortedYears = Object.keys(yearlyIncome).sort((a, b) => b.localeCompare(a));

        const modal = document.createElement('div');
        modal.id = 'historyIncomeModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.style.display = 'flex';
        
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col" style="background-color: #F5F1E8;">
                <div class="p-6 border-b-2 flex justify-between items-center" style="border-color: #4A7C7E; background-color: rgba(74, 124, 126, 0.1);">
                    <h2 style="color: #2C3E50;" class="text-2xl font-bold">Income History</h2>
                    <button onclick="closeHistoryIncomeModal()" class="text-2xl font-bold hover:opacity-70 transition-smooth" style="color: #2C3E50;">&times;</button>
                </div>
                <div class="p-6 overflow-y-auto flex-1">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Monthly Income -->
                        <div>
                            <h3 style="color: #2C3E50;" class="text-xl font-bold mb-4">Monthly Income</h3>
                            ${sortedMonths.length > 0 ? `
                                <div class="space-y-3">
                                    ${sortedMonths.map(monthKey => {
                                        const monthData = monthlyIncome[monthKey];
                                        return `
                                        <div class="p-4 rounded-lg border-2" style="border-color: rgba(74, 124, 126, 0.3); background-color: rgba(74, 124, 126, 0.1);">
                                            <div class="flex justify-between items-center mb-2">
                                                <strong style="color: #2C3E50;">${monthData.label}</strong>
                                                <span style="color: #4A7C7E; font-weight: bold; font-size: 1.1rem;">₱${monthData.total.toLocaleString()}</span>
                                            </div>
                                            <p style="color: rgba(44, 62, 80, 0.7);" class="text-sm">${monthData.bookings.length} paid booking${monthData.bookings.length !== 1 ? 's' : ''}</p>
                                        </div>
                                    `;
                                    }).join('')}
                                </div>
                            ` : `
                                <div class="p-4 rounded-lg border-2 text-center" style="border-color: rgba(74, 124, 126, 0.2); background-color: rgba(74, 124, 126, 0.05);">
                                    <p style="color: rgba(44, 62, 80, 0.7);">No paid bookings yet</p>
                                </div>
                            `}
                        </div>
                        
                        <!-- Yearly Income -->
                        <div>
                            <h3 style="color: #2C3E50;" class="text-xl font-bold mb-4">Yearly Income</h3>
                            ${sortedYears.length > 0 ? `
                                <div class="space-y-3">
                                    ${sortedYears.map(year => {
                                        const yearData = yearlyIncome[year];
                                        return `
                                        <div class="p-4 rounded-lg border-2" style="border-color: rgba(74, 124, 126, 0.3); background-color: rgba(74, 124, 126, 0.1);">
                                            <div class="flex justify-between items-center mb-2">
                                                <strong style="color: #2C3E50;">${year}</strong>
                                                <span style="color: #4A7C7E; font-weight: bold; font-size: 1.1rem;">₱${yearData.total.toLocaleString()}</span>
                                            </div>
                                            <p style="color: rgba(44, 62, 80, 0.7);" class="text-sm">${yearData.bookings.length} paid booking${yearData.bookings.length !== 1 ? 's' : ''}</p>
                                        </div>
                                    `;
                                    }).join('')}
                                </div>
                            ` : `
                                <div class="p-4 rounded-lg border-2 text-center" style="border-color: rgba(74, 124, 126, 0.2); background-color: rgba(74, 124, 126, 0.05);">
                                    <p style="color: rgba(44, 62, 80, 0.7);">No paid bookings yet</p>
                                </div>
                            `}
                        </div>
                    </div>
                    
                    <!-- Total Summary -->
                    ${paidBookings.length > 0 ? `
                    <div class="mt-6 p-4 rounded-lg border-2" style="border-color: #4A7C7E; background-color: rgba(74, 124, 126, 0.1);">
                        <div class="flex justify-between items-center">
                            <strong style="color: #2C3E50;" class="text-xl">Total Income (All Time):</strong>
                            <span style="color: #4A7C7E; font-weight: bold; font-size: 1.5rem;">
                                ₱${paidBookings.reduce((total, booking) => {
                                    const room = property.rooms.find(r => r.id === booking.room_id);
                                    return total + (room ? parseFloat(room.price) : 0);
                                }, 0).toLocaleString()}
                            </span>
                        </div>
                        <p style="color: rgba(44, 62, 80, 0.7);" class="text-sm mt-2">From ${paidBookings.length} paid booking${paidBookings.length !== 1 ? 's' : ''}</p>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeHistoryIncomeModal();
            }
        });
    }

    // Close History Income Modal
    function closeHistoryIncomeModal() {
        const modal = document.getElementById('historyIncomeModal');
        if (modal) {
            modal.remove();
        }
    }

    // Toggle booking payment status
    async function toggleBookingPayment(bookingId, isPaid) {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            alert('Please login to manage payments');
            return;
        }

        const action = isPaid ? 'mark as paid' : 'mark as unpaid';
        if (!confirm(`Are you sure you want to ${action} this booking?`)) {
            return;
        }

        try {
            const response = await fetch(`/api/bookings/${bookingId}`, {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ is_paid: isPaid })
            });

            const data = await response.json();

            if (response.ok) {
                // Reload property to show updated payment status
                loadProperty();
            } else {
                alert(data.message || `Error updating payment status`);
            }
        } catch (error) {
            console.error('Error updating payment status:', error);
            alert(`Error updating payment status. Please try again.`);
        }
    }

    // Toggle room availability (for property owners)
    async function toggleRoomAvailability(roomId, newStatus) {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            alert('Please login to manage rooms');
            return;
        }

        if (!confirm(`Are you sure you want to mark this room as ${newStatus ? 'available' : 'unavailable'}?`)) {
            return;
        }

        try {
            const response = await fetch(`/api/rooms/${roomId}/availability`, {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ is_available: newStatus })
            });

            const data = await response.json();

            if (response.ok) {
                // Reload property to update room status
                loadProperty();
            } else {
                alert(data.message || 'Error updating room availability');
            }
        } catch (error) {
            console.error('Error updating room availability:', error);
            alert('Error updating room availability. Please try again.');
        }
    }

    // Open Pending Bookings Modal
    function openPendingBookingsModal() {
        if (!property || !property.all_bookings) {
            alert('No bookings data available');
            return;
        }

        const pendingBookings = property.all_bookings.filter(b => b.status === 'pending');
        
        if (pendingBookings.length === 0) {
            alert('No pending bookings');
            return;
        }

        const modal = document.createElement('div');
        modal.id = 'pendingBookingsModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.style.display = 'flex';
        
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col" style="background-color: #F5F1E8;">
                <div class="p-6 border-b-2 flex justify-between items-center" style="border-color: #D4A574; background-color: rgba(212, 165, 116, 0.1);">
                    <h2 style="color: #2C3E50;" class="text-2xl font-bold">Pending Bookings (${pendingBookings.length})</h2>
                    <button onclick="closePendingBookingsModal()" class="text-2xl font-bold hover:opacity-70 transition-smooth" style="color: #2C3E50;">&times;</button>
                </div>
                <div class="p-6 overflow-y-auto flex-1">
                    <div class="space-y-4">
                        ${pendingBookings.map(booking => {
                            return `
                            <div class="p-4 rounded-lg border-2" style="border-color: #D4A57440; background-color: rgba(212, 165, 116, 0.15);">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <strong style="color: #2C3E50;" class="text-lg">${booking.user ? booking.user.name : booking.name}</strong>
                                            <span class="px-3 py-1 rounded text-sm font-semibold" style="background-color: #D4A57420; color: #D4A574;">
                                                Pending
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm" style="color: rgba(44, 62, 80, 0.7);">
                                            <p><strong>Email:</strong> ${booking.user ? booking.user.email : booking.email}</p>
                                            <p><strong>Phone:</strong> ${booking.user ? (booking.user.phone || 'N/A') : booking.phone}</p>
                                            ${booking.room ? `<p><strong>Room:</strong> ${booking.room.name}</p>` : ''}
                                            ${booking.check_in_date ? `<p><strong>Check-in:</strong> ${booking.check_in_date}</p>` : ''}
                                            ${booking.check_out_date ? `<p><strong>Check-out:</strong> ${booking.check_out_date}</p>` : ''}
                                        </div>
                                        ${booking.message ? `<p style="color: rgba(44, 62, 80, 0.7);" class="text-sm mt-2 italic p-2 rounded" style="background-color: rgba(44, 62, 80, 0.05);">"${booking.message}"</p>` : ''}
                                    </div>
                                </div>
                                <div class="flex gap-3 mt-4">
                                    <button onclick="updateBookingStatus(${booking.id}, 'approved')" 
                                            style="background-color: #4A7C7E; color: white;" 
                                            class="flex-1 px-6 py-3 rounded-lg font-semibold hover:opacity-90 transition-smooth shadow-md">
                                        ✓ Approve Booking
                                    </button>
                                    <button onclick="updateBookingStatus(${booking.id}, 'rejected')" 
                                            style="background-color: #D2691E; color: white;" 
                                            class="flex-1 px-6 py-3 rounded-lg font-semibold hover:opacity-90 transition-smooth shadow-md">
                                        ✗ Reject Booking
                                    </button>
                                </div>
                            </div>
                        `;
                        }).join('')}
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closePendingBookingsModal();
            }
        });
    }

    // Close Pending Bookings Modal
    function closePendingBookingsModal() {
        const modal = document.getElementById('pendingBookingsModal');
        if (modal) {
            modal.remove();
        }
    }

    // Update booking status (for property owners)
    async function updateBookingStatus(bookingId, status) {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            alert('Please login to manage bookings');
            return;
        }

        const action = status === 'approved' ? 'approve' : 'reject';
        if (!confirm(`Are you sure you want to ${action} this booking?`)) {
            return;
        }

        try {
            const response = await fetch(`/api/bookings/${bookingId}`, {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ status: status })
            });

            const data = await response.json();

            if (response.ok) {
                // Close modal and reload property to show updated booking status
                closePendingBookingsModal();
                loadProperty();
            } else {
                alert(data.message || `Error ${action}ing booking`);
            }
        } catch (error) {
            console.error('Error updating booking status:', error);
            alert(`Error ${action}ing booking. Please try again.`);
        }
    }

    function updateRoomPrice() {
        const roomSelect = document.getElementById('bookingRoomId');
        const roomDesc = document.getElementById('roomDescription');
        const roomCapacityInfo = document.getElementById('roomCapacityInfo');
        
        if (roomSelect) {
            const selectedOption = roomSelect.options[roomSelect.selectedIndex];
            if (selectedOption.value) {
                const room = property.rooms.find(r => r.id == selectedOption.value);
                if (room) {
                    if (roomDesc) {
                        roomDesc.textContent = room.description || '';
                    }
                    
                    if (roomCapacityInfo) {
                        const occupancy = room.occupancy_count || 0;
                        const remaining = room.remaining_capacity !== undefined ? room.remaining_capacity : (room.capacity - occupancy);
                        if (remaining > 0) {
                            roomCapacityInfo.textContent = `After your booking, ${remaining - 1} more spot${remaining - 1 !== 1 ? 's' : ''} will be available in this room.`;
                            roomCapacityInfo.style.color = '#4A7C7E';
                        } else {
                            roomCapacityInfo.textContent = '';
                        }
                    }
                }
            } else {
                if (roomDesc) roomDesc.textContent = '';
                if (roomCapacityInfo) roomCapacityInfo.textContent = '';
            }
        }
    }

    async function submitBooking() {
        const token = localStorage.getItem('auth_token');
        const name = document.getElementById('bookingName').value;
        const email = document.getElementById('bookingEmail').value;
        const phone = document.getElementById('bookingPhone').value;
        const message = document.getElementById('bookingMessage').value;
        const checkIn = document.getElementById('bookingCheckIn').value;
        const checkOut = document.getElementById('bookingCheckOut').value;
        const roomId = document.getElementById('bookingRoomId')?.value || null;
        const errorDiv = document.getElementById('bookingError');

        errorDiv.classList.add('hidden');

        if (!name || !email || !phone) {
            errorDiv.textContent = 'Please ensure all required fields are filled.';
            errorDiv.classList.remove('hidden');
            return;
        }

        const rooms = property.rooms || [];
        if (rooms.length > 0 && !roomId) {
            errorDiv.textContent = 'Please select a room.';
            errorDiv.classList.remove('hidden');
            return;
        }

        try {
            const response = await fetch(`/api/properties/${propertyId}/bookings`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    room_id: roomId,
                    name, 
                    email, 
                    phone, 
                    message: message || null,
                    check_in_date: checkIn || null,
                    check_out_date: checkOut || null
                })
            });

            const data = await response.json();

            if (response.ok) {
                alert('Booking request submitted successfully! The property owner will contact you soon.');
                closeBookingModal();
            } else {
                const errorMsg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Error submitting booking');
                errorDiv.textContent = errorMsg;
                errorDiv.classList.remove('hidden');
            }
        } catch (err) {
            console.error('Error submitting booking:', err);
            errorDiv.textContent = 'Error submitting booking. Please try again.';
            errorDiv.classList.remove('hidden');
        }
    }

    function showReviewForm() {
        const rating = prompt('Rating (1-5):');
        const comment = prompt('Comment (optional):');

        if (rating && rating >= 1 && rating <= 5) {
            const token = localStorage.getItem('auth_token');
            fetch(`/api/properties/${propertyId}/reviews`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ rating: parseInt(rating), comment })
            })
            .then(res => res.json())
            .then(data => {
                alert('Review added successfully!');
                loadProperty();
            })
            .catch(err => {
                console.error('Error adding review:', err);
                alert('Error adding review');
            });
        }
    }

    // Open Edit Property Modal
    function openEditPropertyModal() {
        if (!property) {
            alert('Property data not loaded yet');
            return;
        }
        
        const isOwner = property.is_owner !== undefined ? property.is_owner : (property.user && property.user.id && window.currentUser && window.currentUser.id == property.user.id);
        if (!isOwner) {
            alert('You can only edit your own properties');
            return;
        }
        
        // Reset edit room index
        editRoomIndex = property.rooms ? property.rooms.length : 0;

        const modal = document.createElement('div');
        modal.id = 'editPropertyModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center';
        modal.style.display = 'flex';
        modal.style.zIndex = '9999';
        modal.style.position = 'fixed';
        
        // Pre-fill form with existing property data
        const amenitiesText = property.amenities && Array.isArray(property.amenities) 
            ? property.amenities.join(', ') 
            : (property.amenities || '');
        
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col" style="background-color: #F5F1E8; position: relative; z-index: 10000;">
                <div class="p-6 border-b-2 flex justify-between items-center" style="border-color: #4A7C7E; background-color: rgba(74, 124, 126, 0.1); position: relative; z-index: 10001;">
                    <h2 style="color: #2C3E50;" class="text-2xl font-bold">Edit Property</h2>
                    <button onclick="closeEditPropertyModal()" class="text-2xl font-bold hover:opacity-70 transition-smooth" style="color: #2C3E50;">&times;</button>
                </div>
                <div class="p-6 overflow-y-auto flex-1">
                    <form id="editPropertyForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Title *</label>
                                <input type="text" id="editTitle" name="title" required value="${property.title || ''}"
                                       class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                                       style="border-color: rgba(74, 124, 126, 0.3);"
                                       onfocus="this.style.borderColor='#4A7C7E'" 
                                       onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Description *</label>
                                <textarea id="editDescription" name="description" rows="4" required
                                          class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                                          style="border-color: rgba(74, 124, 126, 0.3);"
                                          onfocus="this.style.borderColor='#4A7C7E'" 
                                          onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">${property.description || ''}</textarea>
                            </div>
                            
                            <div>
                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Price (₱) *</label>
                                <input type="number" id="editPrice" name="price" step="0.01" min="0" required value="${property.price || ''}"
                                       class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                                       style="border-color: rgba(74, 124, 126, 0.3);"
                                       onfocus="this.style.borderColor='#4A7C7E'" 
                                       onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                            </div>
                            
                            <div>
                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Property Type *</label>
                                <select id="editPropertyType" name="property_type" required
                                        class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                                        style="border-color: rgba(74, 124, 126, 0.3);"
                                        onfocus="this.style.borderColor='#4A7C7E'" 
                                        onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                                    <option value="boarding_house" ${property.property_type === 'boarding_house' ? 'selected' : ''}>Boarding House</option>
                                    <option value="apartment" ${property.property_type === 'apartment' ? 'selected' : ''}>Apartment</option>
                                    <option value="dormitory" ${property.property_type === 'dormitory' ? 'selected' : ''}>Dormitory</option>
                                    <option value="other" ${property.property_type === 'other' ? 'selected' : ''}>Other</option>
                                </select>
                            </div>
                            
                            <div>
                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Address *</label>
                                <input type="text" id="editAddress" name="address" required value="${property.address || ''}"
                                       class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                                       style="border-color: rgba(74, 124, 126, 0.3);"
                                       onfocus="this.style.borderColor='#4A7C7E'" 
                                       onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                            </div>
                            
                            <div>
                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">City *</label>
                                <input type="text" id="editCity" name="city" required value="${property.city || ''}"
                                       class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                                       style="border-color: rgba(74, 124, 126, 0.3);"
                                       onfocus="this.style.borderColor='#4A7C7E'" 
                                       onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                            </div>
                            
                            <div>
                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">State</label>
                                <input type="text" id="editState" name="state" value="${property.state || ''}"
                                       class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                                       style="border-color: rgba(74, 124, 126, 0.3);"
                                       onfocus="this.style.borderColor='#4A7C7E'" 
                                       onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                            </div>
                            
                            <div>
                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Zip Code</label>
                                <input type="text" id="editZipCode" name="zip_code" value="${property.zip_code || ''}"
                                       class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                                       style="border-color: rgba(74, 124, 126, 0.3);"
                                       onfocus="this.style.borderColor='#4A7C7E'" 
                                       onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                            </div>
                            
                            <input type="hidden" id="editLatitude" name="latitude" value="${property.latitude || ''}">
                            <input type="hidden" id="editLongitude" name="longitude" value="${property.longitude || ''}">
                            
                            <div>
                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Bedrooms</label>
                                <input type="number" id="editBedrooms" name="bedrooms" min="0" value="${property.bedrooms || ''}"
                                       class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                                       style="border-color: rgba(74, 124, 126, 0.3);"
                                       onfocus="this.style.borderColor='#4A7C7E'" 
                                       onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                            </div>
                            
                            <div>
                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Bathrooms</label>
                                <input type="number" id="editBathrooms" name="bathrooms" min="0" value="${property.bathrooms || ''}"
                                       class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                                       style="border-color: rgba(74, 124, 126, 0.3);"
                                       onfocus="this.style.borderColor='#4A7C7E'" 
                                       onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                            </div>
                            
                            <div>
                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Capacity</label>
                                <input type="number" id="editCapacity" name="capacity" min="1" value="${property.capacity || ''}"
                                       class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                                       style="border-color: rgba(74, 124, 126, 0.3);"
                                       onfocus="this.style.borderColor='#4A7C7E'" 
                                       onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Amenities (comma-separated)</label>
                                <input type="text" id="editAmenities" name="amenities" value="${amenitiesText}"
                                       placeholder="WiFi, Air Conditioning, Parking"
                                       class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                                       style="border-color: rgba(74, 124, 126, 0.3);"
                                       onfocus="this.style.borderColor='#4A7C7E'" 
                                       onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                            </div>
                            
                            <div>
                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Contact Phone</label>
                                <input type="text" id="editContactPhone" name="contact_phone" value="${property.contact_phone || ''}"
                                       class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                                       style="border-color: rgba(74, 124, 126, 0.3);"
                                       onfocus="this.style.borderColor='#4A7C7E'" 
                                       onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                            </div>
                            
                            <div>
                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Contact Email</label>
                                <input type="email" id="editContactEmail" name="contact_email" value="${property.contact_email || ''}"
                                       class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                                       style="border-color: rgba(74, 124, 126, 0.3);"
                                       onfocus="this.style.borderColor='#4A7C7E'" 
                                       onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Images (Max 10MB per image)</label>
                                <input type="file" id="editImages" name="images[]" multiple accept="image/*"
                                       class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-smooth"
                                       style="border-color: rgba(74, 124, 126, 0.3);">
                                <p class="text-sm mt-1" style="color: rgba(44, 62, 80, 0.7);">You can upload new images. Existing images will be kept if no new images are uploaded.</p>
                            </div>
                        </div>

                        <!-- Map Section -->
                        <div class="mt-8 border-t pt-6" style="border-color: rgba(74, 124, 126, 0.2);">
                            <h3 style="color: #2C3E50;" class="text-xl font-bold mb-4">Update Property Location</h3>
                            <p class="text-sm mb-4" style="color: rgba(44, 62, 80, 0.7);">Search for a location or click on the map to update your property's location. You can also drag the marker to adjust the position.</p>
                            
                            <!-- Search Box -->
                            <div class="mb-4 flex gap-2">
                                <input type="text" id="editMapSearch" placeholder="Search for a location (e.g., Tacloban City, Barangay 1)" 
                                       class="flex-1 px-4 py-2 border-2 rounded-lg focus:outline-none transition-smooth"
                                       style="border-color: rgba(74, 124, 126, 0.3);"
                                       onkeypress="if(event.key === 'Enter') searchEditLocation()">
                                <button type="button" onclick="searchEditLocation()" style="background-color: #4A7C7E; color: white;" class="px-6 py-2 rounded-lg font-semibold hover:opacity-90 transition-smooth">
                                    Search
                                </button>
                            </div>
                            
                            <div id="editPropertyMap" style="height: 400px; border-radius: 8px; overflow: hidden; position: relative; z-index: 1;"></div>
                            <p id="editMapStatus" class="text-sm mt-2" style="color: rgba(44, 62, 80, 0.7);">
                                ${property.latitude && property.longitude 
                                    ? `Current location: ${property.latitude}, ${property.longitude}` 
                                    : 'Click on the map to set location'}
                            </p>
                        </div>

                        <!-- Rooms Section -->
                        <div class="mt-8 border-t pt-6" style="border-color: rgba(74, 124, 126, 0.2);">
                            <div class="flex justify-between items-center mb-4">
                                <h3 style="color: #2C3E50;" class="text-xl font-bold">Rooms</h3>
                                <button type="button" onclick="addEditRoom()" style="background-color: #4A7C7E; color: white;" class="px-4 py-2 rounded-lg font-semibold hover:opacity-90 transition-smooth">
                                    + Add Room
                                </button>
                            </div>
                            <div id="editRoomsContainer" class="space-y-4">
                                ${property.rooms && property.rooms.length > 0 ? property.rooms.map((room, index) => `
                                    <div class="p-4 border-2 rounded-lg room-item" style="border-color: rgba(74, 124, 126, 0.3); background-color: rgba(74, 124, 126, 0.05);" data-room-id="${room.id}">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 style="color: #2C3E50;" class="font-semibold">Room ${index + 1}</h4>
                                            <button type="button" onclick="removeEditRoom(this)" style="background-color: #D2691E; color: white;" class="px-3 py-1 rounded text-sm font-semibold hover:opacity-90 transition-smooth">
                                                Remove
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Room Name *</label>
                                                <input type="text" name="rooms[${index}][name]" required value="${room.name || ''}"
                                                       class="w-full px-3 py-2 border-2 rounded-lg"
                                                       style="border-color: rgba(74, 124, 126, 0.3);">
                                                <input type="hidden" name="rooms[${index}][id]" value="${room.id}">
                                            </div>
                                            <div>
                                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Price (₱) *</label>
                                                <input type="number" name="rooms[${index}][price]" step="0.01" min="0" required value="${room.price || ''}"
                                                       class="w-full px-3 py-2 border-2 rounded-lg"
                                                       style="border-color: rgba(74, 124, 126, 0.3);">
                                            </div>
                                            <div>
                                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Capacity *</label>
                                                <input type="number" name="rooms[${index}][capacity]" min="1" required value="${room.capacity || 1}"
                                                       class="w-full px-3 py-2 border-2 rounded-lg"
                                                       style="border-color: rgba(74, 124, 126, 0.3);">
                                            </div>
                                            <div>
                                                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Description</label>
                                                <textarea name="rooms[${index}][description]" rows="2"
                                                          class="w-full px-3 py-2 border-2 rounded-lg"
                                                          style="border-color: rgba(74, 124, 126, 0.3);">${room.description || ''}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                `).join('') : ''}
                            </div>
                        </div>

                        <div id="editPropertyError" class="hidden p-3 rounded-lg mb-4" style="background-color: rgba(210, 105, 30, 0.1); color: #D2691E;"></div>
                        
                        <div class="flex gap-3 mt-6">
                            <button type="submit" style="background-color: #4A7C7E; color: white;" class="flex-1 px-6 py-3 rounded-lg font-semibold shadow-md hover:opacity-90 transition-smooth">
                                Update Property
                            </button>
                            <button type="button" onclick="closeEditPropertyModal()" style="background-color: #2C3E50; color: white;" class="px-6 py-3 rounded-lg font-semibold shadow-md hover:opacity-90 transition-smooth">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Initialize map for edit modal
        setTimeout(() => {
            initEditPropertyMap();
        }, 100);
        
        // Handle form submission
        document.getElementById('editPropertyForm').addEventListener('submit', submitEditProperty);
        
        // Close on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeEditPropertyModal();
            }
        });
    }

    // Initialize map for edit property modal
    let editMap = null;
    let editMarker = null;
    
    function initEditPropertyMap() {
        const mapElement = document.getElementById('editPropertyMap');
        if (!mapElement) {
            console.log('Edit map element not found');
            return;
        }
        
        // Remove existing map if any
        if (editMap) {
            try {
                editMap.remove();
            } catch (e) {
                console.log('Error removing existing map:', e);
            }
        }
        
        // Default to Tacloban City or property location
        const defaultLat = property && property.latitude ? parseFloat(property.latitude) : 11.2444;
        const defaultLng = property && property.longitude ? parseFloat(property.longitude) : 125.0058;
        
        try {
            editMap = L.map('editPropertyMap', {
                zoomControl: true
            }).setView([defaultLat, defaultLng], 13);
        } catch (e) {
            console.error('Error initializing edit map:', e);
            return;
        }
        
        // Force lower z-index for map container and all its children
        const mapContainer = editMap.getContainer();
        if (mapContainer) {
            mapContainer.style.zIndex = '1';
            mapContainer.style.position = 'relative';
            // Set z-index for all map panes to keep them inside modal
            setTimeout(() => {
                if (editMap.getPane('tilePane')) {
                    editMap.getPane('tilePane').style.zIndex = '1';
                }
                if (editMap.getPane('markerPane')) {
                    editMap.getPane('markerPane').style.zIndex = '2';
                }
                if (editMap.getPane('popupPane')) {
                    editMap.getPane('popupPane').style.zIndex = '3';
                }
                if (editMap.getPane('shadowPane')) {
                    editMap.getPane('shadowPane').style.zIndex = '4';
                }
            }, 50);
        }
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(editMap);
        
        // Add marker if location exists
        if (property.latitude && property.longitude) {
            editMarker = L.marker([property.latitude, property.longitude], { draggable: true }).addTo(editMap);
            editMarker.bindPopup(`<strong>${property.title}</strong><br>${property.address}`).openPopup();
        } else {
            // Add marker at default location
            editMarker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(editMap);
        }
        
        // Update coordinates on marker drag
        editMarker.on('dragend', function() {
            const position = editMarker.getLatLng();
            document.getElementById('editLatitude').value = position.lat.toFixed(6);
            document.getElementById('editLongitude').value = position.lng.toFixed(6);
            document.getElementById('editMapStatus').textContent = `Location set: ${position.lat.toFixed(6)}, ${position.lng.toFixed(6)}`;
        });
        
        // Update coordinates on map click
        editMap.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            if (editMarker) {
                editMarker.setLatLng([lat, lng]);
            } else {
                editMarker = L.marker([lat, lng], { draggable: true }).addTo(editMap);
            }
            
            document.getElementById('editLatitude').value = lat.toFixed(6);
            document.getElementById('editLongitude').value = lng.toFixed(6);
            document.getElementById('editMapStatus').textContent = `Location set: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        });
    }

    // Search location for edit map
    async function searchEditLocation() {
        const searchInput = document.getElementById('editMapSearch');
        const query = searchInput.value.trim();
        
        if (!query) {
            alert('Please enter a location to search');
            return;
        }
        
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`);
            const data = await response.json();
            
            if (data && data.length > 0) {
                const result = data[0];
                const lat = parseFloat(result.lat);
                const lng = parseFloat(result.lon);
                
                editMap.setView([lat, lng], 15);
                
                if (editMarker) {
                    editMarker.setLatLng([lat, lng]);
                } else {
                    editMarker = L.marker([lat, lng], { draggable: true }).addTo(editMap);
                }
                
                document.getElementById('editLatitude').value = lat.toFixed(6);
                document.getElementById('editLongitude').value = lng.toFixed(6);
                document.getElementById('editMapStatus').textContent = `Location found: ${result.display_name}`;
                
                // Update address field if possible
                const addressParts = result.display_name.split(',');
                if (addressParts.length > 0) {
                    const addressInput = document.getElementById('editAddress');
                    if (addressInput && !addressInput.value) {
                        addressInput.value = addressParts[0].trim();
                    }
                }
            } else {
                alert('Location not found. Please try a different search term.');
            }
        } catch (error) {
            console.error('Error searching location:', error);
            alert('Error searching location. Please try again.');
        }
    }

    // Close Edit Property Modal
    function closeEditPropertyModal() {
        const modal = document.getElementById('editPropertyModal');
        if (modal) {
            // Clean up map
            if (editMap) {
                editMap.remove();
                editMap = null;
                editMarker = null;
            }
            modal.remove();
        }
    }

    // Add room in edit modal
    function addEditRoom() {
        if (editRoomIndex === 0 && property && property.rooms) {
            editRoomIndex = property.rooms.length;
        }
        const container = document.getElementById('editRoomsContainer');
        const roomDiv = document.createElement('div');
        roomDiv.className = 'p-4 border-2 rounded-lg room-item';
        roomDiv.style.borderColor = 'rgba(74, 124, 126, 0.3)';
        roomDiv.style.backgroundColor = 'rgba(74, 124, 126, 0.05)';
        roomDiv.innerHTML = `
            <div class="flex justify-between items-center mb-3">
                <h4 style="color: #2C3E50;" class="font-semibold">New Room</h4>
                <button type="button" onclick="removeEditRoom(this)" style="background-color: #D2691E; color: white;" class="px-3 py-1 rounded text-sm font-semibold hover:opacity-90 transition-smooth">
                    Remove
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Room Name *</label>
                    <input type="text" name="rooms[${editRoomIndex}][name]" required
                           class="w-full px-3 py-2 border-2 rounded-lg"
                           style="border-color: rgba(74, 124, 126, 0.3);">
                </div>
                <div>
                    <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Price (₱) *</label>
                    <input type="number" name="rooms[${editRoomIndex}][price]" step="0.01" min="0" required
                           class="w-full px-3 py-2 border-2 rounded-lg"
                           style="border-color: rgba(74, 124, 126, 0.3);">
                </div>
                <div>
                    <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Capacity *</label>
                    <input type="number" name="rooms[${editRoomIndex}][capacity]" min="1" required value="1"
                           class="w-full px-3 py-2 border-2 rounded-lg"
                           style="border-color: rgba(74, 124, 126, 0.3);">
                </div>
                <div>
                    <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Description</label>
                    <textarea name="rooms[${editRoomIndex}][description]" rows="2"
                              class="w-full px-3 py-2 border-2 rounded-lg"
                              style="border-color: rgba(74, 124, 126, 0.3);"></textarea>
                </div>
            </div>
        `;
        container.appendChild(roomDiv);
        editRoomIndex++;
    }

    // Remove room in edit modal
    function removeEditRoom(button) {
        button.closest('.room-item').remove();
    }

    // Submit edit property form
    async function submitEditProperty(e) {
        e.preventDefault();
        const token = localStorage.getItem('auth_token');
        if (!token) {
            alert('Please login to edit properties');
            return;
        }

        const errorDiv = document.getElementById('editPropertyError');
        errorDiv.classList.add('hidden');

        const formData = new FormData();
        formData.append('title', document.getElementById('editTitle').value);
        formData.append('description', document.getElementById('editDescription').value);
        formData.append('price', document.getElementById('editPrice').value);
        formData.append('property_type', document.getElementById('editPropertyType').value);
        formData.append('address', document.getElementById('editAddress').value);
        formData.append('city', document.getElementById('editCity').value);
        formData.append('state', document.getElementById('editState').value || '');
        formData.append('zip_code', document.getElementById('editZipCode').value || '');
        formData.append('latitude', document.getElementById('editLatitude').value || '');
        formData.append('longitude', document.getElementById('editLongitude').value || '');
        formData.append('bedrooms', document.getElementById('editBedrooms').value || '');
        formData.append('bathrooms', document.getElementById('editBathrooms').value || '');
        formData.append('capacity', document.getElementById('editCapacity').value || '');
        formData.append('amenities', document.getElementById('editAmenities').value || '');
        formData.append('contact_phone', document.getElementById('editContactPhone').value || '');
        formData.append('contact_email', document.getElementById('editContactEmail').value || '');

        // Add images if any
        const imageInput = document.getElementById('editImages');
        if (imageInput.files.length > 0) {
            for (let i = 0; i < imageInput.files.length; i++) {
                formData.append('images[]', imageInput.files[i]);
            }
        }

        // Add rooms
        const roomItems = document.querySelectorAll('#editRoomsContainer .room-item');
        const rooms = [];
        roomItems.forEach((item, index) => {
            const roomId = item.querySelector('input[type="hidden"][name*="[id]"]')?.value;
            const name = item.querySelector('input[name*="[name]"]').value;
            const price = item.querySelector('input[name*="[price]"]').value;
            const capacity = item.querySelector('input[name*="[capacity]"]').value;
            const description = item.querySelector('textarea[name*="[description]"]')?.value || '';
            
            if (name && price && capacity) {
                rooms.push({
                    id: roomId || null,
                    name: name,
                    price: price,
                    capacity: capacity,
                    description: description
                });
            }
        });
        formData.append('rooms', JSON.stringify(rooms));

        try {
            // Use POST with _method=PUT for FormData compatibility
            formData.append('_method', 'PUT');
            const response = await fetch(`/api/properties/${property.id}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
                body: formData
            });

            let data;
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned an invalid response. Please try again.');
            }

            if (response.ok) {
                alert('Property updated successfully!');
                closeEditPropertyModal();
                // Force full page reload to show updated data
                window.location.reload();
            } else {
                let errorMsg = 'Error updating property';
                if (data.message) {
                    errorMsg = data.message;
                } else if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join(', ');
                }
                errorDiv.textContent = errorMsg;
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error updating property:', error);
            errorDiv.textContent = 'Error updating property. Please try again.';
            errorDiv.classList.remove('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', loadProperty);
</script>
@endsection

