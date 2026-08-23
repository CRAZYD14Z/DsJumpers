
    const LOGIN_URL =  '<?php echo URL_BASE;?>/api/login';
    const API_BASE_URL = '<?php echo URL_BASE;?>/api/';    
    let TOKEN = localStorage.getItem('apiToken'); 

    $('.lang-option').on('click', function(e) {
        e.preventDefault();

        $.ajax({
            url: 'cambiar_idioma.php',
            type: 'POST',
            data: { lang: $(this).data('lang') },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Recargamos para que el servidor lea la nueva sesión de idioma
                    location.reload(); 
                }
            }
        });
        
    });

    $(document).ajaxSuccess(function(event, xhr, settings) {
        const nuevoToken = xhr.getResponseHeader('Authorization-Update');
        if (nuevoToken) {
            localStorage.setItem('apiToken', nuevoToken);
            TOKEN = nuevoToken;
            console.log("Token actualizado globalmente desde: " + settings.url);
        }
    }); 

    $(document).ajaxError(function(event, xhr, settings) {
        if (xhr.status === 401) {
            localStorage.removeItem('apiToken');
            window.location.href = 'logout.php';
        }
    });

    const formatCurrency = (amount) => {
    return Number(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    };    
