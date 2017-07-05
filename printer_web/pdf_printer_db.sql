--
-- Base de datos: `pdf_printer_db`
--
CREATE DATABASE IF NOT EXISTS `pdf_printer_db` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `pdf_printer_db`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajos`
--

CREATE TABLE IF NOT EXISTS `trabajos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `listing_title` varchar(200) DEFAULT NULL,
  `size` varchar(200) DEFAULT NULL,
  `queue_name` varchar(200) DEFAULT NULL,
  `local_server` varchar(200) DEFAULT NULL,
  `customer_name` varchar(200) DEFAULT NULL,
  `owner` varchar(200) DEFAULT NULL,
  `host_name` varchar(200) DEFAULT NULL,
  `device` varchar(200) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pages` varchar(100) DEFAULT NULL,
  `size_pdf` varchar(100) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;