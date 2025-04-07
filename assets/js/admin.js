jQuery(document).ready(function($) {
    console.log('Alisa Admin JS Loaded'); // Debug line

    // Initialize color pickers
    if ($('.wp-color-picker').length) {
        $('.wp-color-picker').wpColorPicker();
        console.log('Color pickers initialized'); // Debug line
    }

    // Handle icon upload
    $('#upload_send_icon').on('click', function(e) {
        console.log('Upload icon clicked'); // Debug line
        e.preventDefault();
        
        var image = wp.media({
            title: 'Upload Send Icon',
            multiple: false
        }).open()
        .on('select', function() {
            var uploaded_image = image.state().get('selection').first();
            var image_url = uploaded_image.toJSON().url;
            
            $('#send_icon_url').val(image_url);
            $('#send_icon_preview').attr('src', image_url).show();
            $('#remove_send_icon').show();
            console.log('Image uploaded:', image_url); // Debug line
        });
    });

    // Handle icon removal
    $('#remove_send_icon').on('click', function(e) {
        console.log('Remove icon clicked'); // Debug line
        e.preventDefault();
        $('#send_icon_url').val('');
        $('#send_icon_preview').hide();
        $(this).hide();
    });
});
