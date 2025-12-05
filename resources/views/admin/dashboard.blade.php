@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6">Admin Dashboard</h1>

    <!-- Statistics -->
    <div id="stats" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-gray-500 text-sm mb-2">Total Properties</h3>
            <p class="text-3xl font-bold" id="totalProperties">-</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-gray-500 text-sm mb-2">Pending Approval</h3>
            <p class="text-3xl font-bold text-yellow-600" id="pendingProperties">-</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-gray-500 text-sm mb-2">Total Users</h3>
            <p class="text-3xl font-bold" id="totalUsers">-</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-gray-500 text-sm mb-2">Total Bookings</h3>
            <p class="text-3xl font-bold" id="totalBookings">-</p>
        </div>
    </div>

    <!-- Pending Properties -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-2xl font-bold mb-4">Pending Properties</h2>
        <div id="pendingPropertiesList">
            <p class="text-gray-500">Loading...</p>
        </div>
    </div>

    <!-- All Properties -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4">All Properties</h2>
        <div class="mb-4">
            <select id="statusFilter" onchange="filterProperties()" class="px-4 py-2 border rounded">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div id="allPropertiesList">
            <p class="text-gray-500">Loading...</p>
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('auth_token');

    async function loadDashboard() {
        try {
            const response = await fetch('/api/admin/dashboard', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const stats = await response.json();
            
            document.getElementById('totalProperties').textContent = stats.total_properties;
            document.getElementById('pendingProperties').textContent = stats.pending_properties;
            document.getElementById('totalUsers').textContent = stats.total_users;
            document.getElementById('totalBookings').textContent = stats.total_bookings;
        } catch (error) {
            console.error('Error loading dashboard:', error);
        }
    }

    async function loadPendingProperties() {
        try {
            const response = await fetch('/api/admin/properties/pending', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const properties = await response.json();
            
            const container = document.getElementById('pendingPropertiesList');
            if (properties.length === 0) {
                container.innerHTML = '<p class="text-gray-500">No pending properties.</p>';
                return;
            }

            container.innerHTML = properties.map(property => `
                <div class="border-b pb-4 mb-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-lg">${property.title}</h3>
                            <p class="text-gray-600">${property.city} - ₱${parseFloat(property.price).toLocaleString()}</p>
                            <p class="text-sm text-gray-500">By: ${property.user.name}</p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="approveProperty(${property.id})" 
                                    class="bg-green-500 hover:bg-green-700 text-white px-4 py-2 rounded">
                                Approve
                            </button>
                            <button onclick="rejectProperty(${property.id})" 
                                    class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded">
                                Reject
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading pending properties:', error);
        }
    }

    async function filterProperties() {
        const status = document.getElementById('statusFilter').value;
        const params = status ? `?status=${status}` : '';
        
        try {
            const response = await fetch(`/api/admin/properties${params}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const data = await response.json();
            const properties = data.data || data;
            
            const container = document.getElementById('allPropertiesList');
            if (properties.length === 0) {
                container.innerHTML = '<p class="text-gray-500">No properties found.</p>';
                return;
            }

            container.innerHTML = properties.map(property => `
                <div class="border-b pb-4 mb-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-lg">${property.title}</h3>
                            <p class="text-gray-600">${property.city} - ₱${parseFloat(property.price).toLocaleString()}</p>
                            <p class="text-sm">
                                <span class="px-2 py-1 rounded ${getStatusColor(property.status)}">${property.status}</span>
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <a href="/properties/${property.id}" 
                               class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                View
                            </a>
                            <button onclick="deleteProperty(${property.id})" 
                                    class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading properties:', error);
        }
    }

    function getStatusColor(status) {
        const colors = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'approved': 'bg-green-100 text-green-800',
            'rejected': 'bg-red-100 text-red-800'
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    }

    async function approveProperty(id) {
        try {
            const response = await fetch(`/api/admin/properties/${id}/approve`, {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (response.ok) {
                alert('Property approved!');
                loadPendingProperties();
                filterProperties();
                loadDashboard();
            }
        } catch (error) {
            console.error('Error approving property:', error);
        }
    }

    async function rejectProperty(id) {
        try {
            const response = await fetch(`/api/admin/properties/${id}/reject`, {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (response.ok) {
                alert('Property rejected!');
                loadPendingProperties();
                filterProperties();
                loadDashboard();
            }
        } catch (error) {
            console.error('Error rejecting property:', error);
        }
    }

    async function deleteProperty(id) {
        if (!confirm('Are you sure you want to delete this property?')) return;
        
        try {
            const response = await fetch(`/api/admin/properties/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (response.ok) {
                alert('Property deleted!');
                filterProperties();
                loadDashboard();
            }
        } catch (error) {
            console.error('Error deleting property:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadDashboard();
        loadPendingProperties();
        filterProperties();
    });
</script>
@endsection

