function updateSeatingPlan() {
    fetch('path_to_your_backend_script')
        .then(response => response.json())
        .then(data => {
            // Update your seating plan here
        })
        .catch(error => console.error('Error:', error));
}

// Real-time updates using WebSockets
const socket = new WebSocket('ws://your_websocket_server');
socket.onmessage = function(event) {
    updateSeatingPlan();
};
