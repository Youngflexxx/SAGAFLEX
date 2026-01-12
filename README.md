Título de la copia barata de Twitter: SAGAFLEX

________________________________________
1. Modelo Lógico de Datos (Arquitectura de Base de Datos)
El diseño se basa en un modelo Relacional, donde la integridad de la información depende de la vinculación correcta entre entidades.
•	Entidad Usuario (Supratabla): Actúa como el eje de identidad. Su lógica principal es la unicidad (username y email no repetidos).
•	Entidad Post (Subtabla): Depende existencialmente de un usuario. Se establece una relación 1:N (Uno a Muchos): un usuario puede tener múltiples posts, pero un post pertenece a un único autor.
•	Integridad Referencial: Se utiliza la lógica de ON DELETE CASCADE. Teóricamente, esto asegura que si un perfil de usuario es eliminado, sus publicaciones no queden como "datos huérfanos" en la base de datos, manteniendo la limpieza del sistema.
________________________________________
2. Lógica del Sistema de Autenticación y Seguridad
El flujo de autenticación no es solo un acceso, sino una gestión de Estado de Sesión.
1.	Criptografía Simétrica de un Solo Sentido: Para el almacenamiento de contraseñas, se aplica la lógica de Hashing. A diferencia del cifrado, el hash no se puede "desencriptar". La validación lógica consiste en hashear la contraseña ingresada en el login y comparar si el resultado coincide con el hash almacenado.
2.	Persistencia de Sesión (Statefulness): PHP utiliza un PHPSESSID único almacenado en una cookie del navegador. En el servidor, este ID se vincula a un archivo temporal que contiene los datos del usuario.
3.	Middleware de Autorización: Es una capa lógica que se interpone entre la petición del usuario y el recurso protegido. Si el token de sesión no existe, la lógica de negocio debe forzar un redireccionamiento (302 Redirect) al punto de entrada (Login).
________________________________________
3. Lógica del CRUD (Ciclo de Vida de la Información)
Para la entidad Post, el flujo lógico es el siguiente:
•	Creación (C): Captura de datos heterogéneos (Texto para el contenido, Enum/String para categorías, Booleano para privacidad).
•	Lectura (R): Implementa lógica de Join Algebraico. Para mostrar un post, el sistema debe unir la tabla Posts con Users para obtener el nombre del autor en una sola operación de lectura.
•	Actualización (U): Requiere una validación de propiedad. Lógicamente: IF (session_user_id == post_author_id) THEN permit_edit.
•	Borrado (D): Implementa una confirmación de estado. A nivel lógico, es una operación destructiva que requiere una señal de seguridad adicional (interfaz de confirmación) para evitar errores accidentales.
________________________________________
4. Lógica de Negocio: Validación Compleja
La regla de negocio inventada: "Restricción de Frecuencia Temporal (Anti-Spam)".
Esta validación no es de formato (como verificar un email), sino de comportamiento. Su lógica se descompone en tres pasos:
1.	Identificación: Localizar al actor (ID del usuario actual).
2.	Cuantificación Temporal: Consultar la base de datos buscando registros del mismo autor cuyo timestamp sea superior a Tiempo_Actual - 10 minutos.
3.	Decisión Binaria: Si el conteo es mayor a $N$ (ej. 3 posts), la aplicación debe disparar una excepción de lógica de negocio, bloqueando el INSERT y retornando un mensaje de error al frontend.
________________________________________
5. Interacción de Tecnologías (The Stack Logic)
•	Backend (PHP): Actúa como el Controlador. Procesa la lógica de servidor, gestiona la conexión con SQLite y aplica las reglas de seguridad. Es el "cerebro" que decide qué datos se guardan y cuáles se rechazan.
•	Base de Datos (SQLite): Es la Capa de Persistencia. Al ser un archivo plano, su lógica de acceso es rápida para aplicaciones de pequeña escala, eliminando la necesidad de un servidor de base de datos independiente.
•	Frontend (PetiteVue): Actúa como la Capa de Reactividad. Su función lógica es la sincronización del DOM. Si un usuario escribe, PetiteVue actualiza el estado interno de la app instantáneamente (ej. contador de caracteres) sin consultar al servidor, mejorando la experiencia de usuario (UX).
