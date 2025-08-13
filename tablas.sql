--Tablas provisionales para la app_ticket

CREATE TABLE formulario_ticket (
    form_tick_num VARCHAR(250) PRIMARY KEY, --Colocarle Unique
    form_tic_usu INT NOT NULL, --Catalogo de la persona que esta teniendo el problema
    tic_dependencia SMALLINT NOT NULL,--Dependencia
    tic_telefono INT NOT NULL, --Telefono de la persona que esta teniendo el problema
    tic_correo_electronico VARCHAR(100) NOT NULL, --Correo electronico de la persona que esta teniendo el problema
    tic_app INT NOT NULL, --Aplicación donde se esta presentando el problema
    tic_comentario_falla TEXT NOT NULL, --Comentario del error
    tic_imagen VARCHAR(250),
    form_fecha_creacion DATETIME YEAR TO SECOND DEFAULT CURRENT YEAR TO SECOND,
    form_estado SMALLINT DEFAULT 1,
    FOREIGN KEY (form_tic_usu) REFERENCES mper(per_catalogo),
    FOREIGN KEY (tic_dependencia) REFERENCES mdep(dep_llave),
    FOREIGN KEY (tic_app) REFERENCES grupo_menuautocom(gma_codigo)
);



CREATE TABLE tickets_asignados (
    tic_id SERIAL PRIMARY KEY,
    tic_numero_ticket VARCHAR(250) NOT NULL, --Ticket del formulario creado donde esta almacenado toda la información del error 
    tic_encargado INT NOT NULL, --Catalgo de la persona encargada
    estado_ticket INT NOT NULL, --Estado en el que se encuentra el ticket
    tic_situacion SMALLINT DEFAULT 1, 
    FOREIGN KEY (tic_numero_ticket) REFERENCES formulario_ticket(form_tick_num),
    FOREIGN KEY (tic_encargado) REFERENCES mper(per_catalogo),
    FOREIGN KEY (estado_ticket) REFERENCES estado_ticket(est_tic_id)
);

CREATE TABLE historial_incidentes_tickets (
    hist_tic_id SERIAL PRIMARY KEY,
    hist_tic_encargado INT NOT NULL, --Catalgo de la persona encargada
    hist_tic_solicitante INT NOT NULL, --Catalogo de la persona que tiene el problema
    hist_ticket VARCHAR(250) NOT NULL,  --Ticket del formulario creado donde esta almacenado toda la información del error
    hist_dependencia SMALLINT NOT NULL,      
    hist_tic_fecha_inicio DATETIME YEAR TO SECOND,
    hist_tic_fecha_finalizacion DATETIME YEAR TO SECOND,
    hist_tic_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (hist_ticket) REFERENCES formulario_ticket(form_tick_num),
    FOREIGN KEY (hist_tic_encargado) REFERENCES mper(per_catalogo),
    FOREIGN KEY (hist_tic_solicitante) REFERENCES mper(per_catalogo),
    FOREIGN KEY (hist_dependencia) REFERENCES mdep(dep_llave)
    
);

CREATE TABLE estado_ticket (
    est_tic_id SERIAL PRIMARY KEY, 
    est_tic_desc VARCHAR (50) NOT NULL, --Descripción de cada uno de los estados del ticket
    est_tic_situacion SMALLINT DEFAULT 1
);
--1-Recibido
--2-En proceso
--3-Resuelto
--4-Rechazado
--5-En espera de requerimientos nuevos (Por si el personal encargado necesita mas informacion)

--TAbla mper_otros




