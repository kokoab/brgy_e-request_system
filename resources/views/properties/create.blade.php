@extends('layouts.app')

@section('title', 'Add Property')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6">Add New Property</h1>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <form id="propertyForm" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                    <input type="text" id="title" name="title" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                    <textarea id="description" name="description" rows="4" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price (₱) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Property Type *</label>
                    <select id="property_type" name="property_type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="boarding_house">Boarding House</option>
                        <option value="apartment">Apartment</option>
                        <option value="dormitory">Dormitory</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                    <input type="text" id="address" name="address" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                    <input type="text" id="city" name="city" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">State</label>
                    <input type="text" id="state" name="state"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Zip Code</label>
                    <input type="text" id="zip_code" name="zip_code"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <!-- Hidden fields for latitude and longitude -->
                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bedrooms</label>
                    <input type="number" id="bedrooms" name="bedrooms" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bathrooms</label>
                    <input type="number" id="bathrooms" name="bathrooms" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Capacity</label>
                    <input type="number" id="capacity" name="capacity" min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amenities (comma-separated)</label>
                    <input type="text" id="amenities" name="amenities" placeholder="WiFi, Air Conditioning, Parking"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                    <input type="text" id="contact_phone" name="contact_phone"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                    <input type="email" id="contact_email" name="contact_email"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Images (Max 10MB per image)</label>
                    <input type="file" id="images" name="images[]" multiple accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <p class="text-sm text-gray-500 mt-1">You can upload multiple images. Each image can be up to 10MB.</p>
                </div>
            </div>

            <!-- Rooms Section -->
            <div class="mt-8 border-t pt-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 style="color: #2C3E50;" class="text-xl font-bold">Rooms</h3>
                    <button type="button" onclick="addRoom()" style="background-color: #4A7C7E; color: white;" class="px-4 py-2 rounded-lg font-semibold hover:opacity-90 transition-smooth">
                        + Add Room
                    </button>
                </div>
                <p class="text-sm text-gray-600 mb-4">Add rooms with different prices and descriptions. Users will be able to book specific rooms.</p>
                <div id="roomsContainer" class="space-y-4">
                    <!-- Rooms will be added here dynamically -->
                </div>
            </div>
            
            <div id="errorMessage" class="text-red-500 text-sm hidden"></div>
            <div id="successMessage" class="text-green-500 text-sm hidden"></div>
            
            <div class="flex gap-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white px-6 py-2 rounded">
                    Submit Property
                </button>
                <a href="/" class="bg-gray-500 hover:bg-gray-700 text-white px-6 py-2 rounded inline-block">
                    Cancel
                </a>
            </div>
        </form>
    </div>
    
    <div class="mt-6 bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold mb-4">Map - Set Your Property Location</h3>
        <p class="text-sm text-gray-600 mb-4">Search for a location or click on the map to mark your property's location. You can also drag the marker to adjust the position.</p>
        
        <!-- Search Box -->
        <div class="mb-4 flex gap-2">
            <input type="text" id="mapSearch" placeholder="Search for a location (e.g., Tacloban City, Barangay 1)" 
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button onclick="searchLocation()" class="bg-blue-500 hover:bg-blue-700 text-white px-6 py-2 rounded">
                Search
            </button>
        </div>
        
        <div id="map" style="height: 400px;"></div>
        <p id="locationStatus" class="text-sm text-gray-500 mt-2"></p>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map;
let marker;

// Initialize map - Tacloban City coordinates
function initMap() {
    map = L.map('map').setView([11.2444, 125.0058], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Function to update marker position
    function updateMarker(lat, lng, address = '') {
        // Set hidden fields
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        
        // Update status message
        const statusEl = document.getElementById('locationStatus');
        if (address) {
            statusEl.textContent = `Location: ${address} (${lat.toFixed(6)}, ${lng.toFixed(6)})`;
        } else {
            statusEl.textContent = `Location set: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        }
        statusEl.classList.remove('text-gray-500');
        statusEl.classList.add('text-green-600');
        
        // Remove existing marker if any
        if (marker) {
            map.removeLayer(marker);
        }
        
        // Add new draggable marker
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        
        // Update position when marker is dragged
        marker.on('dragend', function(e) {
            const position = marker.getLatLng();
            updateMarker(position.lat, position.lng);
            map.setView(position, map.getZoom());
        });
        
        // Center map on marker
        map.setView([lat, lng], map.getZoom());
    }
    
    // Click on map to set location
    map.on('click', function(e) {
        updateMarker(e.latlng.lat, e.latlng.lng);
    });
    
    // Store updateMarker function globally for search
    window.updateMarker = updateMarker;
}

// Search for location using Nominatim (OpenStreetMap geocoding)
async function searchLocation() {
    const searchInput = document.getElementById('mapSearch');
    const query = searchInput.value.trim();
    
    if (!query) {
        alert('Please enter a location to search');
        return;
    }
    
    const statusEl = document.getElementById('locationStatus');
    statusEl.textContent = 'Searching...';
    statusEl.classList.remove('text-green-600');
    statusEl.classList.add('text-blue-600');
    
    try {
        // Use Nominatim geocoding API
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1&countrycodes=ph`);
        const data = await response.json();
        
        if (data && data.length > 0) {
            const result = data[0];
            const lat = parseFloat(result.lat);
            const lng = parseFloat(result.lon);
            const address = result.display_name;
            
            // Update marker position
            if (window.updateMarker) {
                window.updateMarker(lat, lng, address);
            }
        } else {
            statusEl.textContent = 'Location not found. Please try a different search term.';
            statusEl.classList.remove('text-blue-600', 'text-green-600');
            statusEl.classList.add('text-red-600');
        }
    } catch (error) {
        console.error('Geocoding error:', error);
        statusEl.textContent = 'Error searching location. Please try again.';
        statusEl.classList.remove('text-blue-600', 'text-green-600');
        statusEl.classList.add('text-red-600');
    }
}

// Allow Enter key to trigger search
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('mapSearch');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchLocation();
            }
        });
    }
});

let roomCount = 0;

function addRoom() {
    roomCount++;
    const container = document.getElementById('roomsContainer');
    const roomDiv = document.createElement('div');
    roomDiv.className = 'bg-gray-50 p-4 rounded-lg border-2';
    roomDiv.style.borderColor = 'rgba(74, 124, 126, 0.2)';
    roomDiv.id = `room-${roomCount}`;
    roomDiv.innerHTML = `
        <div class="flex justify-between items-center mb-3">
            <h4 style="color: #2C3E50;" class="font-semibold">Room ${roomCount}</h4>
            <button type="button" onclick="removeRoom(${roomCount})" style="color: #D2691E;" class="hover:opacity-80">Remove</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-1">Room Name *</label>
                <input type="text" name="room_name_${roomCount}" id="room_name_${roomCount}" placeholder="e.g., Room 1, Master Bedroom" required
                       class="w-full px-4 py-2 border-2 rounded-lg focus:outline-none transition-smooth"
                       style="border-color: rgba(74, 124, 126, 0.3);"
                       onfocus="this.style.borderColor='#4A7C7E'" 
                       onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
            </div>
            <div>
                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-1">Price (₱) *</label>
                <input type="number" name="room_price_${roomCount}" id="room_price_${roomCount}" step="0.01" min="0" placeholder="0.00" required
                       class="w-full px-4 py-2 border-2 rounded-lg focus:outline-none transition-smooth"
                       style="border-color: rgba(74, 124, 126, 0.3);"
                       onfocus="this.style.borderColor='#4A7C7E'" 
                       onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
            </div>
            <div>
                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-1">Capacity (persons) *</label>
                <input type="number" name="room_capacity_${roomCount}" id="room_capacity_${roomCount}" min="1" value="1" required
                       class="w-full px-4 py-2 border-2 rounded-lg focus:outline-none transition-smooth"
                       style="border-color: rgba(74, 124, 126, 0.3);"
                       onfocus="this.style.borderColor='#4A7C7E'" 
                       onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
            </div>
            <div class="md:col-span-2">
                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-1">Description (Optional)</label>
                <textarea name="room_description_${roomCount}" id="room_description_${roomCount}" rows="2" placeholder="Room features, amenities, etc."
                          class="w-full px-4 py-2 border-2 rounded-lg focus:outline-none transition-smooth"
                          style="border-color: rgba(74, 124, 126, 0.3);"
                          onfocus="this.style.borderColor='#4A7C7E'" 
                          onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'"></textarea>
            </div>
        </div>
    `;
    container.appendChild(roomDiv);
}

function removeRoom(id) {
    const roomDiv = document.getElementById(`room-${id}`);
    if (roomDiv) {
        roomDiv.remove();
    }
}

document.getElementById('propertyForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const errorDiv = document.getElementById('errorMessage');
    const successDiv = document.getElementById('successMessage');
    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');
    
    const token = localStorage.getItem('auth_token');
    if (!token) {
        errorDiv.textContent = 'Please login first to submit a property.';
        errorDiv.classList.remove('hidden');
        setTimeout(() => {
            window.location.href = '/login';
        }, 2000);
        return;
    }
    
    const formData = new FormData();
    formData.append('title', document.getElementById('title').value);
    formData.append('description', document.getElementById('description').value);
    formData.append('price', document.getElementById('price').value);
    formData.append('property_type', document.getElementById('property_type').value);
    formData.append('address', document.getElementById('address').value);
    formData.append('city', document.getElementById('city').value);
    formData.append('state', document.getElementById('state').value);
    formData.append('zip_code', document.getElementById('zip_code').value);
    formData.append('latitude', document.getElementById('latitude').value);
    formData.append('longitude', document.getElementById('longitude').value);
    formData.append('bedrooms', document.getElementById('bedrooms').value);
    formData.append('bathrooms', document.getElementById('bathrooms').value);
    formData.append('capacity', document.getElementById('capacity').value);
    
    const amenities = document.getElementById('amenities').value.split(',').map(a => a.trim()).filter(a => a);
    formData.append('amenities', JSON.stringify(amenities));
    
    formData.append('contact_phone', document.getElementById('contact_phone').value);
    formData.append('contact_email', document.getElementById('contact_email').value);
    
    const images = document.getElementById('images').files;
    for (let i = 0; i < images.length; i++) {
        formData.append('images[]', images[i]);
    }
    
    // Collect rooms data
    const rooms = [];
    for (let i = 1; i <= roomCount; i++) {
        const roomDiv = document.getElementById(`room-${i}`);
        if (roomDiv) {
            const name = document.getElementById(`room_name_${i}`)?.value;
            const price = document.getElementById(`room_price_${i}`)?.value;
            if (name && price) {
                rooms.push({
                    name: name,
                    price: price,
                    capacity: document.getElementById(`room_capacity_${i}`)?.value || 1,
                    description: document.getElementById(`room_description_${i}`)?.value || null
                });
            }
        }
    }
    if (rooms.length > 0) {
        formData.append('rooms', JSON.stringify(rooms));
    }
    
    try {
        const response = await fetch('/api/properties', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', Object.fromEntries(response.headers.entries()));
        
        // Try to parse as JSON
        let data;
        let responseText = '';
        try {
            responseText = await response.text();
            console.log('Response status:', response.status);
            console.log('Response text (first 500 chars):', responseText.substring(0, 500));
            
            // Check if response is empty
            if (!responseText || responseText.trim() === '') {
                throw new Error('Server returned an empty response. Please try again.');
            }
            
            // Check if it's HTML (starts with <)
            if (responseText.trim().startsWith('<')) {
                console.error('HTML response received:', responseText.substring(0, 500));
                // Try to extract error message from HTML if possible
                const errorMatch = responseText.match(/<h1[^>]*>([^<]+)<\/h1>/i) || 
                                  responseText.match(/<title[^>]*>([^<]+)<\/title>/i) ||
                                  responseText.match(/<body[^>]*>([^<]+)/i);
                const errorMsg = errorMatch ? errorMatch[1] : 'Server returned an error page';
                throw new Error(errorMsg + '. Please check your input and try again.');
            }
            
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Failed to parse response:', parseError);
            console.error('Full response text:', responseText);
            
            if (parseError.message && !parseError.message.includes('JSON')) {
                errorDiv.textContent = parseError.message;
            } else {
                errorDiv.textContent = 'Server returned an invalid response. Status: ' + response.status + '. Check console for details.';
            }
            errorDiv.classList.remove('hidden');
            return;
        }
        
        // Handle 401 Unauthenticated
        if (response.status === 401) {
            errorDiv.textContent = 'You are not logged in. Please login and try again.';
            errorDiv.classList.remove('hidden');
            setTimeout(() => {
                window.location.href = '/login';
            }, 2000);
            return;
        }
        
        if (response.ok) {
            successDiv.textContent = 'Property submitted successfully! Your property is now listed.';
            successDiv.classList.remove('hidden');
            setTimeout(() => {
                window.location.href = '/';
            }, 2000);
        } else {
            const errors = data.errors || {};
            const errorMessages = Object.values(errors).flat();
            const errorMsg = errorMessages.length > 0 
                ? errorMessages.join(', ') 
                : (data.message || 'Failed to submit property');
            errorDiv.textContent = errorMsg;
            errorDiv.classList.remove('hidden');
            console.error('Property submission error:', data);
        }
    } catch (error) {
        console.error('Property submission exception:', error);
        if (error.message) {
            errorDiv.textContent = error.message;
        } else {
            errorDiv.textContent = 'An error occurred. Please check your input and try again.';
        }
        errorDiv.classList.remove('hidden');
    }
});

document.addEventListener('DOMContentLoaded', initMap);
</script>
@endsection

