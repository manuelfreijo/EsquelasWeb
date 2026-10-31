-- =====================================================================
-- 02-usuarios.sql — Usuarios da base de datos e permisos
--
-- IMPORTANTE: os contrasinais deste ficheiro deben ser cambiados
-- antes de actualizar a base de datos.
-- =====================================================================

-- Aplicación: só desde o servidor web. Pode xestionar necrolóxicas,
-- pero na auditoría só pode engadir (non ler, modificar nin borrar).
CREATE USER 'app'@'10.1.0.20' IDENTIFIED BY 'XXXX';
GRANT SELECT, INSERT, UPDATE, DELETE ON funeraria.esquelas  TO 'app'@'10.1.0.20';
GRANT INSERT                         ON funeraria.auditoria TO 'app'@'10.1.0.20';

-- Administración da base de datos da aplicación e consulta da auditoría.
CREATE USER 'admin'@'localhost' IDENTIFIED BY 'XXXX';
GRANT ALL PRIVILEGES ON funeraria.* TO 'admin'@'localhost';

-- Copias de seguranza con mariadb-dump: só lectura e bloqueo de táboas.
CREATE USER 'copias'@'localhost' IDENTIFIED BY 'XXXX';
GRANT SELECT, LOCK TABLES, SHOW VIEW, TRIGGER ON funeraria.* TO 'copias'@'localhost';
