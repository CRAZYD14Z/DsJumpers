<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>without bootstrap</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>    	

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>
	<style>

/* Estilo básico para el dropdown de Summernote */
.note-dropdown-menu {
    background-color: white;
    border: 1px solid #ccc;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    padding: 10px;
    list-style: none;
    margin: 0;
    min-width: 150px;
}

.note-dropdown-menu li {
    margin: 0;
    padding: 5px 10px;
}

.note-dropdown-menu li a {
    text-decoration: none;
    color: #333;
    display: block;
}

.note-dropdown-menu li:hover {
    background-color: #f5f5f5;
}

	</style>
  </head>
  <body>
    <div id="summernote"></div>
    <script>

var MiBotonPersonalizado = function (context) {
  var ui = $.summernote.ui;

  // Crear el botón
  var button = ui.button({
    contents: '<i class="fa fa-pencil"/> Mi Botón', // Icono y texto
    tooltip: 'Insertar saludo',                     // Texto al pasar el mouse
    click: function () {
      // Acción al hacer clic: insertar texto en el editor
      context.invoke('editor.insertText', '¡Hola! Este es un texto personalizado.');
    }
  });

  return button.render(); // Retornar el botón como objeto de jQuery
};

var MiListaDesplegable = function (context) {
  var ui = $.summernote.ui;

  // Crear el grupo de botones que contendrá el dropdown
  var button = ui.buttonGroup([
    ui.button({
      className: 'dropdown-toggle',
      contents: 'Plantillas <span class="caret"></span>', // Texto del botón principal
      tooltip: 'Insertar plantillas rápidas',
      data: {
        toggle: 'dropdown'
      }
    }),
    ui.dropdown({
className: 'dropdown-menu note-check', // 'note-check' ayuda con el estilo interno
  contents: [
    '<ul class="note-dropdown-menu">', // Envolver en una lista con clase específica
		'<li><a href="#" data-value="1">Opción 1: Saludo</a></li>',
        '<li><a href="#" data-value="2">Opción 2: Advertencia</a></li>',
        '<li><a href="#" data-value="3">Opción 3: Despedida</a></li>',
    '</ul>'
  ].join(''),
      click: function (event) {
        var $button = $(event.target);
        var value = $button.data('value'); // Obtener el valor de la opción clicada
        
        // Lógica según la opción seleccionada
        if (value === 1) {
          context.invoke('editor.pasteHTML', '<strong>¡Hola! Bienvenido.</strong>');
        } else if (value === 2) {
          context.invoke('editor.pasteHTML', '<div style="color:red;">Acción requerida.</div>');
        } else if (value === 3) {
          context.invoke('editor.pasteHTML', '<em>Atentamente, El Equipo.</em>');
        }
      }
    })
  ]);

  return button.render();
};


      $('#summernote').summernote({
        placeholder: 'Hello stand alone ui',
        tabsize: 2,
        height: 120,
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'underline', 'clear']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link', 'picture', 'video']],
          ['view', ['fullscreen', 'codeview', 'help']],
		  ['mi_grupo', ['miBoton']],
		  ['mygroup', ['miLista']] 
        ],
  buttons: {
    miBoton: MiBotonPersonalizado,
	miLista: MiListaDesplegable
  }
      });



    </script>
  </body>
</html>