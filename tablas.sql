--Tablas provisionales para la app_ticket

CREATE DATABASE app_ticket

CREATE TABLE formulario_ticket (
    form_tick_num VARCHAR(250) PRIMARY KEY,
    form_tic_usu INT NOT NULL, --Catalogo de la persona que esta teniendo el problema
    tic_dependencia INT NOT NULL, --Dependencia
    tic_comentario_falla TEXT NOT NULL, --Comentario del error
    tic_correo_electronico VARCHAR(250) NOT NULL,
    tic_imagen VARCHAR(250),
    form_fecha_creacion DATETIME YEAR TO SECOND DEFAULT CURRENT YEAR TO SECOND,
    FOREIGN KEY (form_tic_usu) REFERENCES mper(per_catalogo),
    FOREIGN KEY (tic_dependencia) REFERENCES mdep(dep_llave)
);

CREATE TABLE tickets_asignados (
    tic_id SERIAL PRIMARY KEY,
    tic_numero_ticket VARCHAR(250) NOT NULL, --Ticket del formulario creado donde esta almacenado toda la información del error 
    tic_encargado INT NOT NULL, --Catalgo de la persona encargada
    estado_ticket INT NOT NULL, --Estado en el que se encuentra el ticket
    FOREIGN KEY (tic_numero_ticket) REFERENCES formulario_ticket(form_tick_num),
    FOREIGN KEY (tic_encargado) REFERENCES mper(per_catalogo),
    FOREIGN KEY (estado_ticket) REFERENCES estado_ticket(est_tic_id)
);

CREATE TABLE historial_incidentes_tickets (
    hist_tic_id SERIAL PRIMARY KEY,
    hist_tic_encargado INT NOT NULL, --Catalgo de la persona encargada
    hist_tic_solicitante INT NOT NULL, --Catalogo de la persona que tiene el problema
    hist_ticket VARCHAR(250) NOT NULL,  --Ticket del formulario creado donde esta almacenado toda la información del error
    hist_dependencia INT NOT NULL,      
    hist_tic_fecha_inicio DATETIME YEAR TO SECOND,
    hist_tic_fecha_finalizacion DATETIME YEAR TO SECOND,
    FOREIGN KEY (hist_ticket) REFERENCES formulario_ticket(form_tick_num),
    FOREIGN KEY (hist_tic_encargado) REFERENCES mper(per_catalogo),
    FOREIGN KEY (hist_tic_solicitante) REFERENCES mper(per_catalogo),
    FOREIGN KEY (hist_dependencia) REFERENCES mdep(dep_llave)
    
);

CREATE TABLE estado_ticket (
    est_tic_id SERIAL PRIMARY KEY, 
    est_tic_desc VARCHAR (50) NOT NULL --Descripción de cada uno de los estados del ticket  
);
--1-Creado
--2-Pendiente de asignacion
--3-Asignado
--4-Resuelto 
--5-En espera de requerimientos nuevoos (Por si el personal encargado necesita mas informacion)



--Mdep
CREATE TABLE informix.mdep  ( 
	dep_llave    	SMALLINT NOT NULL,
	dep_desc_lg  	CHAR(100) NOT NULL,
	dep_desc_md  	CHAR(35) NOT NULL,
	dep_desc_ct  	CHAR(15) NOT NULL,
	dep_clase    	CHAR(1),
	dep_precio   	CHAR(1),
	dep_ejto     	CHAR(1),
	dep_latitud  	VARCHAR(255),
	dep_longitud 	VARCHAR(255),
	dep_ruta_logo	VARCHAR(255),
	dep_situacion	INTEGER,
	PRIMARY KEY(dep_llave)
	ENABLED
)
LOCK MODE ROW
GO


--Mper
CREATE TABLE informix.mper  ( 
	per_catalogo     	INTEGER NOT NULL,
	per_serie        	CHAR(8),
	per_grado        	SMALLINT NOT NULL,
	per_arma         	SMALLINT NOT NULL,
	per_nom1         	CHAR(15),
	per_nom2         	CHAR(15),
	per_ape1         	CHAR(15),
	per_ape2         	CHAR(15),
	per_ape3         	CHAR(15),
	per_ced_ord      	CHAR(4) NOT NULL,
	per_ced_reg      	CHAR(20),
	per_fec_ext_ced  	DATE,
	per_ext_ced_lugar	CHAR(4) NOT NULL,
	per_est_civil    	CHAR(1),
	per_direccion    	CHAR(50),
	per_zona         	SMALLINT,
	per_dir_lugar    	CHAR(4) NOT NULL,
	per_telefono     	INTEGER,
	per_sexo         	CHAR(1) NOT NULL,
	per_fec_nac      	DATE NOT NULL,
	per_nac_lugar    	CHAR(4) NOT NULL,
	per_promocion    	SMALLINT,
	per_afil_ipm     	CHAR(1) NOT NULL,
	per_sangre       	CHAR(3) NOT NULL,
	per_antiguedad   	SMALLINT,
	per_bienal       	SMALLINT NOT NULL,
	per_plaza        	INTEGER NOT NULL,
	per_desc_empleo  	CHAR(45) NOT NULL,
	per_fec_nomb     	DATE NOT NULL,
	per_ord_gral     	CHAR(7),
	per_punto_og     	SMALLINT,
	per_situacion    	CHAR(2) NOT NULL,
	per_prima_prof   	SMALLINT,
	per_dpi          	CHAR(15),
	PRIMARY KEY(per_catalogo)
	ENABLED
)
LOCK MODE ROW
GO
ALTER TABLE informix.mper
	ADD CONSTRAINT ( CHECK (per_est_civil IN ('S' ,'C' ,'U' ,'D' ,'V' )) CONSTRAINT ck_per_est_civil
	ENABLED )
GO
ALTER TABLE informix.mper
	ADD CONSTRAINT ( FOREIGN KEY(per_plaza)
	REFERENCES informix.morg(org_plaza) CONSTRAINT org_plaza
	ENABLED )
GO
ALTER TABLE informix.mper
	ADD CONSTRAINT ( FOREIGN KEY(per_situacion)
	REFERENCES informix.situaciones(sit_codigo) CONSTRAINT fk_mper_situ
	ENABLED )
GO
ALTER TABLE informix.mper
	ADD CONSTRAINT ( FOREIGN KEY(per_dir_lugar)
	REFERENCES informix.dep_mun(dm_codigo) CONSTRAINT fk_mper_dep_mu
	ENABLED )
GO
ALTER TABLE informix.mper
	ADD CONSTRAINT ( FOREIGN KEY(per_nac_lugar)
	REFERENCES informix.dep_mun(dm_codigo) CONSTRAINT fk_mper_dep_m2
	ENABLED )
GO
ALTER TABLE informix.mper
	ADD CONSTRAINT ( FOREIGN KEY(per_ext_ced_lugar)
	REFERENCES informix.dep_mun(dm_codigo) CONSTRAINT fk_mper_dep_m
	ENABLED )
GO
ALTER TABLE informix.mper
	ADD CONSTRAINT ( FOREIGN KEY(per_arma)
	REFERENCES informix.armas(arm_codigo) CONSTRAINT fk_mper_arm
	ENABLED )
GO
ALTER TABLE informix.mper
	ADD CONSTRAINT ( FOREIGN KEY(per_grado)
	REFERENCES informix.grados(gra_codigo) CONSTRAINT fk_mer_grad
	ENABLED )
GO
