jQuery(document).ready(function($) {
    function geoLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        } else {
            alert("Geolocation data is not supported by your browser");
        }
    }
    
    function showPosition(position) {
        var coordinates = {
            'latitude': position.coords.latitude,
            'longitude': position.coords.longitude
        }

        $.ajax({
            url: frtwp_ajax_object.ajaxurl,
            data: {
                'action': 'FRTWPGetUserCoordinates',
                'coordinates': coordinates
            }
        })
        
        return coordinates
    }

    geoLocation()
})