<?php

return [
    'navigation' => [
        'group' => 'Sistema',
        'label' => 'Editor .Env',
    ],

    'page' => [
        'title' => 'Editor .Env',
    ],
    'tabs' => [
        'current-env' => [
            'title' => 'Actual .env',
        ],
        'backups' => [
            'title' => 'Copias de seguridad',
        ],
    ],
    'actions' => [
        'add' => [
            'title' => 'Agregar nueva entrada',
            'modalHeading' => 'Agregar nueva entrada',
            'success' => [
                'title' => 'La clave ":Name" fue escrita exitosamente',
            ],
            'form' => [
                'fields' => [
                    'key' => 'clave',
                    'value' => 'valor',
                    'index' => 'Insertar después de la clave existente (opcional)',
                ],
                'helpText' => [
                    'index' => 'En caso de que necesites colocar esta nueva entrada después de una existente, puedes elegir una clave existente',
                ],
            ],
        ],
        'edit' => [
            'tooltip' => 'Editar entrada ":name"',
            'modal' => [
                'text' => 'Editar entrada',
            ],
        ],
        'delete' => [
            'tooltip' => 'Eliminar la entrada ":name"',
            'confirm' => [
                'title' => 'Estás a punto de eliminar permanentemente ":name". ¿Estás seguro de esta eliminación?',
            ],
        ],
        'clear-cache' => [
            'title' => 'Limpiar cachés',
            'tooltip' => 'A veces Laravel almacena en caché las variables ENV, por lo que necesitas limpiar todas las cachés ("artisan optimize:clear") para que se vuelvan a leer los cambios en .env',
        ],

        'backup' => [
            'title' => 'Crear una nueva copia de seguridad',
            'success' => [
                'title' => 'La copia de seguridad se creó exitosamente',
            ],
        ],
        'download' => [
            'title' => 'Descargar .env actual',
            'tooltip' => 'Descargar el archivo de copia de seguridad ":name"',
        ],
        'upload-backup' => [
            'title' => 'Subir un archivo de copia de seguridad',
        ],
        'show-content' => [
            'modalHeading' => 'Contenido en bruto de la copia de seguridad ":name"',
            'tooltip' => 'Mostrar contenido en bruto',
        ],
        'restore-backup' => [
            'confirm' => [
                'title' => 'Vas a restaurar ":name" en lugar del archivo ".env" actual. Por favor confirma tu elección',
            ],
            'modalSubmit' => 'Restaurar',
            'tooltip' => 'Restaurar ":name" como el ENV actual',
        ],
        'delete-backup' => [
            'tooltip' => 'Eliminar el archivo de copia de seguridad ":name"',
            'confirm' => [
                'title' => 'Estás a punto de eliminar permanentemente el archivo de copia de seguridad ":name". ¿Estás seguro de esta eliminación?',
            ],
        ],
    ],
];
