-- =====================================================================
-- 01-esquema.sql — Base de datos e táboas da aplicación EsquelasWeb
-- (utf8mb4: tiles, «ñ» e calquera carácter)
-- =====================================================================

-- Base de datos para EsquelasWeb
CREATE DATABASE funeraria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE funeraria;

-- Necrolóxicas
CREATE TABLE esquelas (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome_completo    VARCHAR(200) NOT NULL,
  alcume           VARCHAR(100),
  idade            TINYINT UNSIGNED,
  localidade       VARCHAR(100),
  data_falecemento DATE NOT NULL,
  lugar_velorio    VARCHAR(150),
  data_funeral     DATE NOT NULL,
  hora_funeral     TIME NOT NULL,
  lugar_funeral    VARCHAR(150) NOT NULL,
  lugar_enterro    VARCHAR(150),
  familiares       TEXT,
  mensaxe          TEXT,
  -- INDEXAMOS para realiza a consulta pública (data_funeral >= CURDATE())
  INDEX idx_data_funeral (data_funeral),
  -- Impide que a data un funeral sexa anterior ao falecementoa
  CONSTRAINT chk_datas CHECK (data_funeral >= data_falecemento)
) ENGINE=InnoDB;

-- Rexistro de auditoria. Sen clave foránea:
-- consérvase o rexistor inda que se elimine a necrolóxica.
CREATE TABLE auditoria (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario          VARCHAR(50) NOT NULL,
  accion           ENUM('crear','modificar','eliminar') NOT NULL,
  id_esquela       INT UNSIGNED NOT NULL,
  ip_orixe         VARCHAR(45),
  data_hora        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  datos_anteriores JSON,
  INDEX idx_esquela (id_esquela),
  INDEX idx_usuario (usuario)
) ENGINE=InnoDB;
