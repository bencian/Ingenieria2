SET FOREIGN_KEY_CHECKS=0;
  CREATE TABLE `tipo_doc` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nombre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

  INSERT INTO tipo_doc (id, nombre) VALUES (1, 'DNI');
  INSERT INTO tipo_doc (id, nombre) VALUES (2, 'LC');
  INSERT INTO tipo_doc (id, nombre) VALUES (3, 'LE');
  INSERT INTO tipo_doc (id, nombre) VALUES (4, 'Pasaporte');

  CREATE TABLE `pedido` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nombre_apellido` varchar(255) NOT NULL,
    `tipo_doc_id` int(10) NOT NULL, -- Referencia al codigo de la tabla de tipo_doc
    `numero` int(11) NOT NULL,
    `direccion` varchar(255) NOT NULL,
    `carta` text NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT FK_tipo_doc_id FOREIGN KEY (tipo_doc_id) REFERENCES tipo_doc(id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

  INSERT INTO pedido (nombre_apellido, tipo_doc_id, numero, direccion, carta)
  VALUES ('Juan Perez', 1, 27654897, 'Avenida Siempreviva 742', 'Querido Papá Noel');
  INSERT INTO pedido (nombre_apellido, tipo_doc_id, numero, direccion, carta)
  VALUES ('Lionel Messi', 1, 33016244, 'Rosario 234', 'Querido Papá Noel');
  INSERT INTO pedido (nombre_apellido, tipo_doc_id, numero, direccion, carta)
  VALUES ('Juan Carlos', 2, 27855859, 'Areanales 189', 'Querido Papá Noel');


  CREATE TABLE `usuario` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `usuario` varchar(50) NOT NULL,
    `clave` varchar(255) NOT NULL,
    `nombre` varchar(100) NOT NULL,
    `apellido` varchar(100) NOT NULL,
    `mail` varchar(45) NOT NULL,
    PRIMARY KEY (id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET FOREIGN_KEY_CHECKS=1;