-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-07-2025 a las 02:17:05
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gestion_practicas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaciones`
--

CREATE TABLE `asignaciones` (
  `id` int(11) NOT NULL,
  `maestro_id` int(11) NOT NULL,
  `materia_id` int(11) NOT NULL,
  `carrera_id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asignaciones`
--

INSERT INTO `asignaciones` (`id`, `maestro_id`, `materia_id`, `carrera_id`, `grupo_id`) VALUES
(98, 93, 11, 1, 1),
(99, 93, 23, 2, 1),
(100, 93, 35, 6, 1),
(110, 93, 18, 1, 362);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carreras`
--

CREATE TABLE `carreras` (
  `id` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carreras`
--

INSERT INTO `carreras` (`id`, `Nombre`) VALUES
(1, 'Ingeniería en Sistemas Computacionales'),
(2, 'Ingeniería Civil'),
(3, 'Ingeniería Industrial'),
(4, 'Licenciatura en Administración'),
(5, 'Ingeniería en Mecatrónica'),
(6, 'Licenciatura en Biología'),
(7, 'Licenciatura en Gastronomia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

CREATE TABLE `documentos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `archivo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `documentos`
--

INSERT INTO `documentos` (`id`, `titulo`, `archivo`) VALUES
(1, 'FO-TESH-98_simulacion_20250527.pdf', 'FO-TESH-98_simulacion_20250527.pdf'),
(2, 'FO-TESH-98_simulacion_20250527.pdf', 'FO-TESH-98_simulacion_20250527.pdf'),
(3, 'FO-TESH-98_administracion_de_bases_de_datos_20250527.pdf', 'FO-TESH-98_administracion_de_bases_de_datos_20250527.pdf'),
(4, 'FO-TESH-98_simulacion_20250527.pdf', 'FOTESH/Sistemas/juan/FO-TESH-98_simulacion_20250527.pdf'),
(5, 'FO-TESH-98_simulacion_20250527.pdf', 'FOTESH/General/juan/FO-TESH-98_simulacion_20250527.pdf'),
(6, 'FO-TESH-98_simulacion_20250527.pdf', 'FOTESH/Sistemas/juan/FO-TESH-98_simulacion_20250527.pdf'),
(7, 'FO-TESH-98_simulacion_20250527.pdf', 'FOTESH/Sistemas/juan/FO-TESH-98_simulacion_20250527.pdf');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fotesh`
--

CREATE TABLE `fotesh` (
  `id` int(11) NOT NULL,
  `Nombre_Practica` varchar(150) NOT NULL,
  `Objetivo` text NOT NULL,
  `Laboratorio` varchar(100) NOT NULL,
  `Horario` datetime NOT NULL,
  `Fecha_Propuesta` date NOT NULL,
  `Fecha_Real` date DEFAULT NULL,
  `Tipo_de_Laboratorio` varchar(50) NOT NULL,
  `Materia_id` int(11) NOT NULL,
  `Maestro_id` int(11) NOT NULL,
  `pdf_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fotesh`
--

INSERT INTO `fotesh` (`id`, `Nombre_Practica`, `Objetivo`, `Laboratorio`, `Horario`, `Fecha_Propuesta`, `Fecha_Real`, `Tipo_de_Laboratorio`, `Materia_id`, `Maestro_id`, `pdf_id`) VALUES
(3, 'Base ', 'base', 'LME', '0000-00-00 00:00:00', '2025-07-12', '2025-07-11', ' ', 11, 93, NULL),
(4, 'Base ', 'base', 'LEE', '0000-00-00 00:00:00', '2025-07-15', '2025-07-24', ' ', 11, 93, 43),
(5, 'Base ', 'base', 'LEE', '0000-00-00 00:00:00', '2025-07-22', '2025-07-25', ' ', 11, 93, 44),
(7, 'Base ', 'base', 'LH', '0000-00-00 00:00:00', '2025-07-08', '2025-07-29', ' ', 11, 93, 46);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos`
--

CREATE TABLE `grupos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `carrera_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `grupos`
--

INSERT INTO `grupos` (`id`, `nombre`, `fecha_creacion`, `creado_por`, `activo`, `carrera_id`) VALUES
(1, '3602', '2025-05-20 23:39:56', NULL, 1, 1),
(254, '1101', '2025-07-08 16:25:21', NULL, 1, 2),
(255, '1102', '2025-07-08 16:25:21', NULL, 1, 2),
(256, '1153', '2025-07-08 16:25:21', NULL, 1, 2),
(257, '1154', '2025-07-08 16:25:21', NULL, 1, 2),
(258, '1201', '2025-07-08 16:25:21', NULL, 1, 2),
(259, '1202', '2025-07-08 16:25:21', NULL, 1, 2),
(260, '1253', '2025-07-08 16:25:21', NULL, 1, 2),
(261, '1254', '2025-07-08 16:25:21', NULL, 1, 2),
(262, '1301', '2025-07-08 16:25:21', NULL, 1, 2),
(263, '1302', '2025-07-08 16:25:21', NULL, 1, 2),
(264, '1353', '2025-07-08 16:25:21', NULL, 1, 2),
(265, '1354', '2025-07-08 16:25:21', NULL, 1, 2),
(266, '1401', '2025-07-08 16:25:21', NULL, 1, 2),
(267, '1402', '2025-07-08 16:25:21', NULL, 1, 2),
(268, '1453', '2025-07-08 16:25:21', NULL, 1, 2),
(269, '1454', '2025-07-08 16:25:21', NULL, 1, 2),
(270, '1501', '2025-07-08 16:25:21', NULL, 1, 2),
(271, '1502', '2025-07-08 16:25:21', NULL, 1, 2),
(272, '1553', '2025-07-08 16:25:21', NULL, 1, 2),
(273, '1554', '2025-07-08 16:25:21', NULL, 1, 2),
(274, '1601', '2025-07-08 16:25:21', NULL, 1, 2),
(275, '1602', '2025-07-08 16:25:21', NULL, 1, 2),
(276, '1653', '2025-07-08 16:25:21', NULL, 1, 2),
(277, '1654', '2025-07-08 16:25:21', NULL, 1, 2),
(278, '1701', '2025-07-08 16:25:21', NULL, 1, 2),
(279, '1702', '2025-07-08 16:25:21', NULL, 1, 2),
(280, '1753', '2025-07-08 16:25:21', NULL, 1, 2),
(281, '1754', '2025-07-08 16:25:21', NULL, 1, 2),
(282, '1801', '2025-07-08 16:25:21', NULL, 1, 2),
(283, '1802', '2025-07-08 16:25:21', NULL, 1, 2),
(284, '1853', '2025-07-08 16:25:21', NULL, 1, 2),
(285, '1854', '2025-07-08 16:25:21', NULL, 1, 2),
(286, '1901', '2025-07-08 16:25:21', NULL, 1, 2),
(287, '1902', '2025-07-08 16:25:21', NULL, 1, 2),
(288, '1953', '2025-07-08 16:25:21', NULL, 1, 2),
(289, '1954', '2025-07-08 16:25:21', NULL, 1, 2),
(290, '7101', '2025-07-08 16:25:21', NULL, 1, 5),
(291, '7102', '2025-07-08 16:25:21', NULL, 1, 5),
(292, '7153', '2025-07-08 16:25:21', NULL, 1, 5),
(293, '7154', '2025-07-08 16:25:21', NULL, 1, 5),
(294, '7201', '2025-07-08 16:25:21', NULL, 1, 5),
(295, '7202', '2025-07-08 16:25:21', NULL, 1, 5),
(296, '7253', '2025-07-08 16:25:21', NULL, 1, 5),
(297, '7254', '2025-07-08 16:25:21', NULL, 1, 5),
(298, '7301', '2025-07-08 16:25:21', NULL, 1, 5),
(299, '7302', '2025-07-08 16:25:21', NULL, 1, 5),
(300, '7353', '2025-07-08 16:25:21', NULL, 1, 5),
(301, '7354', '2025-07-08 16:25:21', NULL, 1, 5),
(302, '7401', '2025-07-08 16:25:21', NULL, 1, 5),
(303, '7402', '2025-07-08 16:25:21', NULL, 1, 5),
(304, '7453', '2025-07-08 16:25:21', NULL, 1, 5),
(305, '7454', '2025-07-08 16:25:21', NULL, 1, 5),
(306, '7501', '2025-07-08 16:25:21', NULL, 1, 5),
(307, '7502', '2025-07-08 16:25:21', NULL, 1, 5),
(308, '7553', '2025-07-08 16:25:21', NULL, 1, 5),
(309, '7554', '2025-07-08 16:25:21', NULL, 1, 5),
(310, '7601', '2025-07-08 16:25:21', NULL, 1, 5),
(311, '7602', '2025-07-08 16:25:21', NULL, 1, 5),
(312, '7653', '2025-07-08 16:25:21', NULL, 1, 5),
(313, '7654', '2025-07-08 16:25:21', NULL, 1, 5),
(314, '7701', '2025-07-08 16:25:21', NULL, 1, 5),
(315, '7702', '2025-07-08 16:25:21', NULL, 1, 5),
(316, '7753', '2025-07-08 16:25:21', NULL, 1, 5),
(317, '7754', '2025-07-08 16:25:21', NULL, 1, 5),
(318, '7801', '2025-07-08 16:25:21', NULL, 1, 5),
(319, '7802', '2025-07-08 16:25:21', NULL, 1, 5),
(320, '7853', '2025-07-08 16:25:21', NULL, 1, 5),
(321, '7854', '2025-07-08 16:25:21', NULL, 1, 5),
(322, '7901', '2025-07-08 16:25:21', NULL, 1, 5),
(323, '7902', '2025-07-08 16:25:21', NULL, 1, 5),
(324, '7953', '2025-07-08 16:25:21', NULL, 1, 5),
(325, '7954', '2025-07-08 16:25:21', NULL, 1, 5),
(326, '4101', '2025-07-08 16:25:21', NULL, 1, 3),
(327, '4102', '2025-07-08 16:25:21', NULL, 1, 3),
(328, '4153', '2025-07-08 16:25:21', NULL, 1, 3),
(329, '4154', '2025-07-08 16:25:21', NULL, 1, 3),
(330, '4201', '2025-07-08 16:25:21', NULL, 1, 3),
(331, '4202', '2025-07-08 16:25:21', NULL, 1, 3),
(332, '4253', '2025-07-08 16:25:21', NULL, 1, 3),
(333, '4254', '2025-07-08 16:25:21', NULL, 1, 3),
(334, '4301', '2025-07-08 16:25:21', NULL, 1, 3),
(335, '4302', '2025-07-08 16:25:21', NULL, 1, 3),
(336, '4353', '2025-07-08 16:25:21', NULL, 1, 3),
(337, '4354', '2025-07-08 16:25:21', NULL, 1, 3),
(338, '4401', '2025-07-08 16:25:21', NULL, 1, 3),
(339, '4402', '2025-07-08 16:25:21', NULL, 1, 3),
(340, '4453', '2025-07-08 16:25:21', NULL, 1, 3),
(341, '4454', '2025-07-08 16:25:21', NULL, 1, 3),
(342, '4501', '2025-07-08 16:25:21', NULL, 1, 3),
(343, '4502', '2025-07-08 16:25:21', NULL, 1, 3),
(344, '4553', '2025-07-08 16:25:21', NULL, 1, 3),
(345, '4554', '2025-07-08 16:25:21', NULL, 1, 3),
(346, '4601', '2025-07-08 16:25:21', NULL, 1, 3),
(347, '4602', '2025-07-08 16:25:21', NULL, 1, 3),
(348, '4653', '2025-07-08 16:25:21', NULL, 1, 3),
(349, '4654', '2025-07-08 16:25:21', NULL, 1, 3),
(350, '4701', '2025-07-08 16:25:21', NULL, 1, 3),
(351, '4702', '2025-07-08 16:25:21', NULL, 1, 3),
(352, '4753', '2025-07-08 16:25:21', NULL, 1, 3),
(353, '4754', '2025-07-08 16:25:21', NULL, 1, 3),
(354, '4801', '2025-07-08 16:25:21', NULL, 1, 3),
(355, '4802', '2025-07-08 16:25:21', NULL, 1, 3),
(356, '4853', '2025-07-08 16:25:21', NULL, 1, 3),
(357, '4854', '2025-07-08 16:25:21', NULL, 1, 3),
(358, '4901', '2025-07-08 16:25:21', NULL, 1, 3),
(359, '4902', '2025-07-08 16:25:21', NULL, 1, 3),
(360, '4953', '2025-07-08 16:25:21', NULL, 1, 3),
(361, '4954', '2025-07-08 16:25:21', NULL, 1, 3),
(362, '3101', '2025-07-08 16:25:21', NULL, 1, 1),
(363, '3102', '2025-07-08 16:25:21', NULL, 1, 1),
(364, '3153', '2025-07-08 16:25:21', NULL, 1, 1),
(365, '3154', '2025-07-08 16:25:21', NULL, 1, 1),
(366, '3201', '2025-07-08 16:25:21', NULL, 1, 1),
(367, '3202', '2025-07-08 16:25:21', NULL, 1, 1),
(368, '3253', '2025-07-08 16:25:21', NULL, 1, 1),
(369, '3254', '2025-07-08 16:25:21', NULL, 1, 1),
(370, '3301', '2025-07-08 16:25:21', NULL, 1, 1),
(371, '3302', '2025-07-08 16:25:21', NULL, 1, 1),
(372, '3353', '2025-07-08 16:25:21', NULL, 1, 1),
(373, '3354', '2025-07-08 16:25:21', NULL, 1, 1),
(374, '3401', '2025-07-08 16:25:21', NULL, 1, 1),
(375, '3402', '2025-07-08 16:25:21', NULL, 1, 1),
(376, '3453', '2025-07-08 16:25:21', NULL, 1, 1),
(377, '3454', '2025-07-08 16:25:21', NULL, 1, 1),
(378, '3501', '2025-07-08 16:25:21', NULL, 1, 1),
(379, '3502', '2025-07-08 16:25:21', NULL, 1, 1),
(380, '3553', '2025-07-08 16:25:21', NULL, 1, 1),
(381, '3554', '2025-07-08 16:25:21', NULL, 1, 1),
(382, '3601', '2025-07-08 16:25:21', NULL, 1, 1),
(383, '3653', '2025-07-08 16:25:21', NULL, 1, 1),
(384, '3654', '2025-07-08 16:25:21', NULL, 1, 1),
(385, '3701', '2025-07-08 16:25:21', NULL, 1, 1),
(386, '3702', '2025-07-08 16:25:21', NULL, 1, 1),
(387, '3753', '2025-07-08 16:25:21', NULL, 1, 1),
(388, '3754', '2025-07-08 16:25:21', NULL, 1, 1),
(389, '3801', '2025-07-08 16:25:21', NULL, 1, 1),
(390, '3802', '2025-07-08 16:25:21', NULL, 1, 1),
(391, '3853', '2025-07-08 16:25:21', NULL, 1, 1),
(392, '3854', '2025-07-08 16:25:21', NULL, 1, 1),
(393, '3901', '2025-07-08 16:25:21', NULL, 1, 1),
(394, '3902', '2025-07-08 16:25:21', NULL, 1, 1),
(395, '3953', '2025-07-08 16:25:21', NULL, 1, 1),
(396, '3954', '2025-07-08 16:25:21', NULL, 1, 1),
(397, '5101', '2025-07-08 16:25:21', NULL, 1, 4),
(398, '5102', '2025-07-08 16:25:21', NULL, 1, 4),
(399, '5153', '2025-07-08 16:25:21', NULL, 1, 4),
(400, '5154', '2025-07-08 16:25:21', NULL, 1, 4),
(401, '5201', '2025-07-08 16:25:21', NULL, 1, 4),
(402, '5202', '2025-07-08 16:25:21', NULL, 1, 4),
(403, '5253', '2025-07-08 16:25:21', NULL, 1, 4),
(404, '5254', '2025-07-08 16:25:21', NULL, 1, 4),
(405, '5301', '2025-07-08 16:25:21', NULL, 1, 4),
(406, '5302', '2025-07-08 16:25:21', NULL, 1, 4),
(407, '5353', '2025-07-08 16:25:21', NULL, 1, 4),
(408, '5354', '2025-07-08 16:25:21', NULL, 1, 4),
(409, '5401', '2025-07-08 16:25:21', NULL, 1, 4),
(410, '5402', '2025-07-08 16:25:21', NULL, 1, 4),
(411, '5453', '2025-07-08 16:25:21', NULL, 1, 4),
(412, '5454', '2025-07-08 16:25:21', NULL, 1, 4),
(413, '5501', '2025-07-08 16:25:21', NULL, 1, 4),
(414, '5502', '2025-07-08 16:25:21', NULL, 1, 4),
(415, '5553', '2025-07-08 16:25:21', NULL, 1, 4),
(416, '5554', '2025-07-08 16:25:21', NULL, 1, 4),
(417, '5601', '2025-07-08 16:25:21', NULL, 1, 4),
(418, '5602', '2025-07-08 16:25:21', NULL, 1, 4),
(419, '5653', '2025-07-08 16:25:21', NULL, 1, 4),
(420, '5654', '2025-07-08 16:25:21', NULL, 1, 4),
(421, '5701', '2025-07-08 16:25:21', NULL, 1, 4),
(422, '5702', '2025-07-08 16:25:21', NULL, 1, 4),
(423, '5753', '2025-07-08 16:25:21', NULL, 1, 4),
(424, '5754', '2025-07-08 16:25:21', NULL, 1, 4),
(425, '5801', '2025-07-08 16:25:21', NULL, 1, 4),
(426, '5802', '2025-07-08 16:25:21', NULL, 1, 4),
(427, '5853', '2025-07-08 16:25:21', NULL, 1, 4),
(428, '5854', '2025-07-08 16:25:21', NULL, 1, 4),
(429, '5901', '2025-07-08 16:25:21', NULL, 1, 4),
(430, '5902', '2025-07-08 16:25:21', NULL, 1, 4),
(431, '5953', '2025-07-08 16:25:21', NULL, 1, 4),
(432, '5954', '2025-07-08 16:25:21', NULL, 1, 4),
(433, '2101', '2025-07-08 16:25:21', NULL, 1, 6),
(434, '2102', '2025-07-08 16:25:21', NULL, 1, 6),
(435, '2153', '2025-07-08 16:25:21', NULL, 1, 6),
(436, '2154', '2025-07-08 16:25:21', NULL, 1, 6),
(437, '2201', '2025-07-08 16:25:21', NULL, 1, 6),
(438, '2202', '2025-07-08 16:25:21', NULL, 1, 6),
(439, '2253', '2025-07-08 16:25:21', NULL, 1, 6),
(440, '2254', '2025-07-08 16:25:21', NULL, 1, 6),
(441, '2301', '2025-07-08 16:25:21', NULL, 1, 6),
(442, '2302', '2025-07-08 16:25:21', NULL, 1, 6),
(443, '2353', '2025-07-08 16:25:21', NULL, 1, 6),
(444, '2354', '2025-07-08 16:25:21', NULL, 1, 6),
(445, '2401', '2025-07-08 16:25:21', NULL, 1, 6),
(446, '2402', '2025-07-08 16:25:21', NULL, 1, 6),
(447, '2453', '2025-07-08 16:25:21', NULL, 1, 6),
(448, '2454', '2025-07-08 16:25:21', NULL, 1, 6),
(449, '2501', '2025-07-08 16:25:21', NULL, 1, 6),
(450, '2502', '2025-07-08 16:25:21', NULL, 1, 6),
(451, '2553', '2025-07-08 16:25:21', NULL, 1, 6),
(452, '2554', '2025-07-08 16:25:21', NULL, 1, 6),
(453, '2601', '2025-07-08 16:25:21', NULL, 1, 6),
(454, '2602', '2025-07-08 16:25:21', NULL, 1, 6),
(455, '2653', '2025-07-08 16:25:21', NULL, 1, 6),
(456, '2654', '2025-07-08 16:25:21', NULL, 1, 6),
(457, '2701', '2025-07-08 16:25:21', NULL, 1, 6),
(458, '2702', '2025-07-08 16:25:21', NULL, 1, 6),
(459, '2753', '2025-07-08 16:25:21', NULL, 1, 6),
(460, '2754', '2025-07-08 16:25:21', NULL, 1, 6),
(461, '2801', '2025-07-08 16:25:21', NULL, 1, 6),
(462, '2802', '2025-07-08 16:25:21', NULL, 1, 6),
(463, '2853', '2025-07-08 16:25:21', NULL, 1, 6),
(464, '2854', '2025-07-08 16:25:21', NULL, 1, 6),
(465, '2901', '2025-07-08 16:25:21', NULL, 1, 6),
(466, '2902', '2025-07-08 16:25:21', NULL, 1, 6),
(467, '2953', '2025-07-08 16:25:21', NULL, 1, 6),
(468, '2954', '2025-07-08 16:25:21', NULL, 1, 6),
(469, '9101', '2025-07-08 16:25:21', NULL, 1, 7),
(470, '9102', '2025-07-08 16:25:21', NULL, 1, 7),
(471, '9153', '2025-07-08 16:25:21', NULL, 1, 7),
(472, '9154', '2025-07-08 16:25:21', NULL, 1, 7),
(473, '9201', '2025-07-08 16:25:21', NULL, 1, 7),
(474, '9202', '2025-07-08 16:25:21', NULL, 1, 7),
(475, '9253', '2025-07-08 16:25:21', NULL, 1, 7),
(476, '9254', '2025-07-08 16:25:21', NULL, 1, 7),
(477, '9301', '2025-07-08 16:25:21', NULL, 1, 7),
(478, '9302', '2025-07-08 16:25:21', NULL, 1, 7),
(479, '9353', '2025-07-08 16:25:21', NULL, 1, 7),
(480, '9354', '2025-07-08 16:25:21', NULL, 1, 7),
(481, '9401', '2025-07-08 16:25:21', NULL, 1, 7),
(482, '9402', '2025-07-08 16:25:21', NULL, 1, 7),
(483, '9453', '2025-07-08 16:25:21', NULL, 1, 7),
(484, '9454', '2025-07-08 16:25:21', NULL, 1, 7),
(485, '9501', '2025-07-08 16:25:21', NULL, 1, 7),
(486, '9502', '2025-07-08 16:25:21', NULL, 1, 7),
(487, '9553', '2025-07-08 16:25:21', NULL, 1, 7),
(488, '9554', '2025-07-08 16:25:21', NULL, 1, 7),
(489, '9601', '2025-07-08 16:25:21', NULL, 1, 7),
(490, '9602', '2025-07-08 16:25:21', NULL, 1, 7),
(491, '9653', '2025-07-08 16:25:21', NULL, 1, 7),
(492, '9654', '2025-07-08 16:25:21', NULL, 1, 7),
(493, '9701', '2025-07-08 16:25:21', NULL, 1, 7),
(494, '9702', '2025-07-08 16:25:21', NULL, 1, 7),
(495, '9753', '2025-07-08 16:25:21', NULL, 1, 7),
(496, '9754', '2025-07-08 16:25:21', NULL, 1, 7),
(497, '9801', '2025-07-08 16:25:21', NULL, 1, 7),
(498, '9802', '2025-07-08 16:25:21', NULL, 1, 7),
(499, '9853', '2025-07-08 16:25:21', NULL, 1, 7),
(500, '9854', '2025-07-08 16:25:21', NULL, 1, 7),
(501, '9901', '2025-07-08 16:25:21', NULL, 1, 7),
(502, '9902', '2025-07-08 16:25:21', NULL, 1, 7),
(503, '9953', '2025-07-08 16:25:21', NULL, 1, 7),
(504, '9954', '2025-07-08 16:25:21', NULL, 1, 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jdivision`
--

CREATE TABLE `jdivision` (
  `id` int(11) NOT NULL,
  `jefe_id` int(11) NOT NULL,
  `carrera_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maestros`
--

CREATE TABLE `maestros` (
  `ID` int(100) NOT NULL,
  `NOMBRE` varchar(200) NOT NULL,
  `carrera` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `maestros`
--

INSERT INTO `maestros` (`ID`, `NOMBRE`, `carrera`) VALUES
(1, 'Raymundo Rojas Acevedo', 'Ingeniería en Sistemas'),
(2, 'Guillermo Mancilla Benitez', 'Ingeniería en Sistemas'),
(3, 'Francisco Flores Álvarez', 'Ingeniería en Sistemas'),
(4, 'Manuel Peralta Gutierrez', 'Ingeniería en Sistemas'),
(5, 'Armando Ortiz Ramirez', 'Ingeniería en Sistemas'),
(6, 'Analy Garcia Ibañez', 'Ingeniería en Sistemas'),
(7, 'Miguel Angel Sanchez Zuñiga', 'Ingeniería en Sistemas'),
(8, 'Ing, Víctor Esteban Santiago Trejo', 'Ingeniería en Sistemas'),
(9, 'Juan Francisco Juarez Cerda', 'Ingeniería en Sistemas'),
(10, 'Luis Alberto Gonzalez Cervantes', 'Ingeniería en Sistemas'),
(11, 'Lydia Villavicencio Gomez', 'Ingeniería en Sistemas'),
(12, 'Eleazar Alonso Villeda', 'Ingeniería Civil'),
(13, 'Nancy Perez Gutierrez', 'Ingeniería Civil'),
(14, 'Manuel Peralta Gutierrez', 'Ingeniería Civil'),
(15, 'Armando Ortiz Ramirez', 'Ingeniería Civil'),
(16, 'Analy Garcia Ibañez', 'Ingeniería Civil'),
(17, 'Miguel Angel Sanchez Zuñiga', 'Ingeniería Civil'),
(18, 'Ing, Víctor Esteban Santiago Trejo', 'Ingeniería Civil'),
(19, 'Juan Francisco Juarez Cerda', 'Ingeniería Civil'),
(20, 'Luis Alberto Gonzalez Cervantes', 'Ingeniería Civil'),
(21, 'Ing, Víctor Esteban Santiago Trejo', 'Ingeniería Industrial'),
(22, 'J.D.Aurelio Rico Díaz', 'Ingeniería Industrial'),
(23, 'Lydia Villavicencio Gomez', 'Ingeniería Industrial'),
(24, 'Mtra. Lydia Villavicencio Gómez', 'Licenciatura en Administración'),
(25, 'Lic. Juana Sarahi González González', 'Licenciatura en Administración'),
(26, 'Enrique García Trinidad', 'Ingeniería Mecatrónica'),
(27, 'Francisco Flores Alvarez', 'Ingeniería Mecatrónica'),
(28, 'José Rafael Garcia Sáncez', 'Ingeniería Mecatrónica'),
(29, 'Brayan Adrian Navarrete Maltos', 'Ingeniería Mecatrónica'),
(30, 'Ing, Víctor Esteban Santiago Trejo', 'Ingeniería Mecatrónica'),
(31, 'M. en C. Mónica Elias Gónzalez', 'Licenciatura de Biología'),
(32, 'Raúl Aguilar Ríos', 'Licenciatura de Biología'),
(33, 'M. en C. Mónica Elias Gonzalez', 'Licenciatura de Biología'),
(34, 'Francisco Dionisio López Gómez', 'Licenciatura de Biología');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

CREATE TABLE `materias` (
  `id` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id`, `Nombre`) VALUES
(1, 'Programación Orientada a Objetos'),
(2, 'Contabilidad Financiera'),
(3, 'Probabilidad y Estadística'),
(4, 'Simulación'),
(5, 'Métodos Numéricos'),
(6, 'Fundamentos de Bases de Datos'),
(7, 'Tópicos Avanzados de Programación'),
(8, 'Redes de Computadoras'),
(9, 'Lenguajes y Autómatas I'),
(10, 'Lenguajes de Interfaz'),
(11, 'Administración de Bases de Datos'),
(12, 'Ingeniería en Software'),
(13, 'Taller de Sistemas Operativos'),
(14, 'Administración de Redes'),
(15, 'Programación Lógica y Funcional'),
(16, 'Programación Web'),
(17, 'Data Warehouse'),
(18, 'Big Data y NoSQL'),
(19, 'Modelos de Optimización de Recursos'),
(20, 'Dinámica'),
(21, 'Maquinaria Pesada y Movimiento de Tierra'),
(22, 'Administración de la Construcción'),
(23, 'Hidráulica de Canales'),
(24, 'Diseño y Construcción de Pavimentos'),
(25, 'Análisis Estructural'),
(26, 'Dibujo Industrial'),
(27, 'Algoritmos y Lenguajes de Programación'),
(28, 'Tecnologías de la Información Aplicadas a las Finanzas'),
(29, 'Programación Básica'),
(30, 'Álgebra Lineal'),
(31, 'Diseño de Elementos Mecánicos'),
(32, 'Manufactura Avanzada'),
(33, 'Control'),
(34, 'Meteorología y Climatología'),
(35, 'Bioestadística'),
(36, 'Taller de Investigación I'),
(37, 'Sistema de Información Geográfica y Percepción Remota');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pdfs`
--

CREATE TABLE `pdfs` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL,
  `carrera` varchar(100) DEFAULT NULL,
  `materia` varchar(100) DEFAULT NULL,
  `estado` varchar(50) DEFAULT 'no realizada',
  `grupo` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pdfs`
--

INSERT INTO `pdfs` (`id`, `nombre`, `ruta`, `fecha`, `usuario_id`, `carrera`, `materia`, `estado`, `grupo`) VALUES
(14, 'H.pdf', 'PDFS/H.pdf', '2025-04-09 00:00:55', NULL, NULL, NULL, 'no realizada', NULL),
(22, 'fff.pdf', 'PDFS/fff.pdf', '2025-04-22 06:26:43', 60, 'Ingeniería en Sistemas', 'Probabilidad y Estadística', 'no realizada', NULL),
(23, 'fff.pdf', 'PDFS/fff.pdf', '2025-04-22 06:27:59', 60, 'Ingeniería Industrial', 'Dibujo Industrial', 'no realizada', NULL),
(24, 'prueba2.pdf', 'PDFS/prueba2.pdf', '2025-04-22 06:55:12', 60, 'Licenciatura en Biología', 'Bioestadística', 'no realizada', NULL),
(25, 'fff.pdf', 'PDFS/fff.pdf', '2025-04-22 06:56:07', 60, 'Ingeniería Civil', 'Dibujo Industrial', 'no realizada', NULL),
(26, 'fff (1).pdf', 'PDFS/fff (1).pdf', '2025-04-23 03:29:16', 60, 'Ingeniería en Sistemas', 'Probabilidad y Estadística', 'no realizada', '3502'),
(27, 'prueba2.pdf', 'PDFS/prueba2.pdf', '2025-04-23 03:49:21', 60, 'Ingeniería en Sistemas', 'Probabilidad y Estadística', 'no realizada', '3502'),
(28, 'fff (1).pdf', 'PDFS/fff (1).pdf', '2025-04-23 07:23:25', 60, 'Ingeniería en Sistemas', 'Tópicos Avanzados de Programación', 'no realizada', '3502'),
(36, 'FO-TESH-98_simulacion_20250530.pdf', 'FOTESH/Ingeniería en Sistemas Computacionales/juan/FO-TESH-98_simulacion_20250530.pdf', '2025-05-30 17:13:50', 93, 'Ingeniería en Sistemas Computacionales', 'Sin_Materia', 'no realizada', 'Sin_Grupo'),
(39, 'FO-TESH-98_simulacion_20250603.pdf', 'FOTESH/Ingeniería en Sistemas Computacionales/juan/FO-TESH-98_simulacion_20250603.pdf', '2025-06-03 21:26:31', 93, 'Ingeniería en Sistemas Computacionales', 'Sin_Materia', 'no realizada', 'Sin_Grupo'),
(40, 'FO-TESH-98_simulacion_20250604.pdf', 'FOTESH/Ingeniería en Sistemas Computacionales/juan/FO-TESH-98_simulacion_20250604.pdf', '2025-06-04 19:23:37', 93, 'Ingeniería en Sistemas Computacionales', 'Sin_Materia', 'no realizada', 'Sin_Grupo'),
(41, 'FO-TESH-98_11_20250708.pdf', 'FOTESH/juan/FO-TESH-98_11_20250708.pdf', '2025-07-09 05:28:08', 93, '1', 'Administración de Bases de Datos', 'pendiente', '1'),
(42, 'FO-TESH-98_11_20250708.pdf', 'FOTESH/juan/FO-TESH-98_11_20250708.pdf', '2025-07-09 05:39:12', 93, '1', 'Administración de Bases de Datos', 'pendiente', '1'),
(43, 'FO-TESH-98_11_20250708.pdf', 'FOTESH/juan/FO-TESH-98_11_20250708.pdf', '2025-07-09 06:10:38', 93, '1', 'Administración de Bases de Datos', 'pendiente', '1'),
(44, 'FO-TESH-98_11_20250708.pdf', 'FOTESH/juan/FO-TESH-98_11_20250708.pdf', '2025-07-09 06:21:54', 93, 'Ingeniería en Sistemas Computacionales', 'Administración de Bases de Datos', 'pendiente', '1'),
(45, 'FO-TESH-98_administracion_de_bases_de_datos_20250708.pdf', 'FOTESH/juan/FO-TESH-98_administracion_de_bases_de_datos_20250708.pdf', '2025-07-09 06:31:14', 93, 'Ingeniería en Sistemas Computacionales', 'Administración de Bases de Datos', 'pendiente', '1'),
(46, 'FO-TESH-98_administracion_de_bases_de_datos_20250708.pdf', 'FOTESH/juan/FO-TESH-98_administracion_de_bases_de_datos_20250708.pdf', '2025-07-09 06:39:52', 93, 'Ingeniería en Sistemas Computacionales', 'Administración de Bases de Datos', 'pendiente', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `practicas`
--

CREATE TABLE `practicas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `objetivo` text NOT NULL,
  `laboratorio` varchar(50) NOT NULL,
  `horario` varchar(50) NOT NULL,
  `fechas` date NOT NULL,
  `tipo_laboratorio` varchar(50) NOT NULL,
  `materia` varchar(100) NOT NULL,
  `maestro` varchar(100) NOT NULL,
  `Grupo` varchar(50) NOT NULL,
  `estado` enum('pendiente','realizada','eliminada') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipodeusuarios`
--

CREATE TABLE `tipodeusuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('maestro','administrador','jefe_carrera') NOT NULL DEFAULT 'maestro',
  `estado` enum('pendiente','activo','rechazado') DEFAULT 'pendiente',
  `carrera_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipodeusuarios`
--

INSERT INTO `tipodeusuarios` (`id`, `nombre`, `email`, `password`, `rol`, `estado`, `carrera_id`) VALUES
(57, 'Juan José Santiago Ricardo ', 'L22090689@huixquilucan.tecnm.mx', '$2y$10$z5nPTa8sv5SxonIMcc7j6.Zx7.RKPGihTU2zPKLWcRoqs2ATWvf1G', 'administrador', 'activo', NULL),
(70, 'Raymundo Rojas Acevedo', 'raymundo.r.a@huixquilucan.tecnm.mx', '$2y$10$M6ZOCdpR3eVVnJ/g9OQ15uN/FFiJHKHL3iMo000Sm7e2nKMdv6sMm', 'maestro', 'activo', NULL),
(71, 'Guillermo Mancilla Benitez', 'guillermo.m.b@huixquilucan.tecnm.mx', '$2y$10$W29oiTOU5HrM74uo.umxnenb1bXB7BcdDn21xku9x7vzyUWvc6ubS', 'maestro', 'activo', NULL),
(72, 'Francisco Flores Álvarez', 'francisco.f.a@huixquilucan.tecnm.mx', '$2y$10$9Fxp/IEeUfhkNMoExvI/UOibwUokeDIf1HOBZCZBdfIEgXuM8SU3C', 'maestro', 'activo', NULL),
(73, 'Manuel Peralta Gutierrez', 'manuel.p.g@huixquilucan.tecnm.mx', '$2y$10$fIac7dDlZH14AMHuM1LSQuXBDtcj7BDSG3Ehxrj1D9GVXclYnP6ay', 'maestro', 'activo', NULL),
(74, 'Miguel Angel Sanchez Zuñiga', 'miguel.s.z@huixquilucan.tecnm.mx', '$2y$10$XpjitBxQ2aNNnLF5vNvaee97w1BRYROlc.pPwcoX.yh3Nctj91yVW', 'maestro', 'activo', NULL),
(75, 'Víctor Esteban Santiago Trejo', 'victor.s.t@huixquilucan.tecnm.mx', '$2y$10$GPzCkpDCGDHQOJMOubbdU.c8a3ipS62HyqcC1jQo/O3P7Lwr91rPC', 'maestro', 'activo', NULL),
(76, 'Juan Francisco Juarez Cerda', 'juan.j.c@huixquilucan.tecnm.mx', '$2y$10$tabDVOMbWujR7Be1pxizaO095YJS7NyLEF0C6nupuJi81ooYlqdju', 'maestro', 'activo', NULL),
(77, 'Luis Alberto Gonzalez Cervantes', 'luis.g.c@huixquilucan.tecnm.mx', '$2y$10$ZabKg03QaRY2zeU31p0j.eTo21QYW6f6khNfYnzKg/6Er.9afhHMi', 'maestro', 'activo', NULL),
(78, 'Lydia Villavicencio Gomez', 'lydia.v.g@huixquilucan.tecnm.mx', '$2y$10$GcRyfdlElsDymJMQ3s4jg.6Bbo69nP9sTJobmJYsTd6WIvcVz7h3i', 'maestro', 'activo', NULL),
(79, 'Eleazar Alonso Villeda', 'eleazar.a.v@huixquilucan.tecnm.mx', '$2y$10$Vij4.IBGNz8A4h2Aqv42O.6pqUEZJsbT/Xjm7rrN8Bz4emtVfiQEG', 'maestro', 'activo', NULL),
(80, 'Nancy Perez Gutierrez', 'nancy.p.g@huixquilucan.tecnm.mx', '$2y$10$oboin44L9LIcYWUthtBxSOgXSJ4BSHnAw6nbQQr88ha45ujl2r./m', 'maestro', 'activo', NULL),
(81, 'J.D. Aurelio Rico Díaz', 'aurelio.r.d@huixquilucan.tecnm.mx', '$2y$10$N.gp1ScMld/iG.jYE3hEz.ePGJzWrU3/pQFeACze759h52GUQO/wS', 'maestro', 'activo', NULL),
(82, 'Juana Sarahi González González', 'juana.g.g@huixquilucan.tecnm.mx', '$2y$10$wh64H7gNSlvC1WJmFeJw3uzPfoBCi3FOiS1ehdEgffKP2FVwCd7vy', 'maestro', 'activo', NULL),
(83, 'Armando Ortiz Ramirez', 'armando.o.r@huixquilucan.tecnm.mx', '$2y$10$cSDtcZchfCttu3bvSJ/YUOIz9Jik9zkauviIV4HW5QxHUrYAkGkUO', 'maestro', 'activo', NULL),
(84, 'Analy Garcia Ibañez', 'analy.g.i@huixquilucan.tecnm.mx', '$2y$10$UbJPsFFSXdqRCNIKm2LXmeUegVnJMCnYqHCSTK92MnDRshnfRzbyC', 'maestro', 'activo', NULL),
(86, 'Néstor Monrroy Méndez	', 'division.sistemas@huixquilucan.tecnm.mx', '$2y$10$W0iJmcVIh0ejIu1GO8vf.uYewFeth1qW/PbAlrKJhO4VmWo1OPg6y', 'jefe_carrera', 'activo', 1),
(87, 'Juan Antonio Benitez', 'division.industrial@huixquilucan.tecnm.mx', '$2y$10$u6IC2oeRJhpOK0KkVS6AMOo5RCtGI.9831iATi/fxMOJE79MUY3lS', 'jefe_carrera', 'activo', 3),
(88, 'Margarita Tellez', 'division.biologia@huixquilucan.tecnm.mx', '$2y$10$HCKGvL8AojYGAgPj62e0f.xbPV6m4aWlFHzGY70Jg5zWdq8NzQIk.', 'jefe_carrera', 'activo', 6),
(89, 'Enrique Ortiz Candelaria', 'division.civil@huixquilucan.tecnm.mx', '$2y$10$0.KySPng1YFr8sJuWZ2W7ezSDqpoepQy7KJ1xTi55xrTvhqsU5hsW', 'jefe_carrera', 'activo', 2),
(90, 'Jesús Martínez ', 'division.mecatronica@huixquilucan.tecnm.mx', '$2y$10$nA3rcCsJ6xmmZ5rvAI9/ouuTV.qyZpH.ngXrT3b71lAGXf/vGllKm', 'jefe_carrera', 'activo', 5),
(91, 'Micaela Velázquez Torres', 'division.administracion@huixquilucan.tecnm.mx', '$2y$10$WKrOSRcQkfCRx22//tsbkO5Nll29lKUjg7VNUGNi8w6LzxJyHwUmC', 'jefe_carrera', 'activo', 4),
(92, 'Maria Guadalupe Monica Carrera Barrios', 'division.gastronomia@huixquilucan.tecnm.mx', '$2y$10$j/FQNhIY57WM2iAZ9ZTMNOsieSBkadD1gObGq.dlcJq8kGSSITbOy', 'jefe_carrera', 'activo', 7),
(93, 'juan', '12345@huixquilucan.tecnm.mx', '$2y$10$WYOpieYrlGFQCEGUTpUpnOmi7cwQIfiG6c1HZY8S2X7EbxLGnbvoG', 'maestro', 'activo', 1),
(94, 'juan jose ', '123456@huixquilucan.tecnm.mx', '$2y$10$v/GORD5BKhTwIKEe8PqKrOKALnXNpfx6IFUH.lu3zsgK7aAzOBJEe', 'jefe_carrera', 'activo', 1),
(95, 'jefed', '1@huixquilucan.tecnm.mx', '$2y$10$nEHrK.imIdncgHjG.SxyEeeEIFGAQKDPALzH.Zxj3Jzy65kxJ2xbW', 'jefe_carrera', 'activo', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maestro_id` (`maestro_id`),
  ADD KEY `materia_id` (`materia_id`),
  ADD KEY `carrera_id` (`carrera_id`),
  ADD KEY `grupo_id` (`grupo_id`);

--
-- Indices de la tabla `carreras`
--
ALTER TABLE `carreras`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `fotesh`
--
ALTER TABLE `fotesh`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Materia_id` (`Materia_id`),
  ADD KEY `Maestro_id` (`Maestro_id`);

--
-- Indices de la tabla `grupos`
--
ALTER TABLE `grupos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uc_nombre_grupo` (`nombre`),
  ADD KEY `fk_grupos_carrera` (`carrera_id`);

--
-- Indices de la tabla `jdivision`
--
ALTER TABLE `jdivision`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jefe_id` (`jefe_id`),
  ADD KEY `carrera_id` (`carrera_id`);

--
-- Indices de la tabla `maestros`
--
ALTER TABLE `maestros`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pdfs`
--
ALTER TABLE `pdfs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `practicas`
--
ALTER TABLE `practicas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipodeusuarios`
--
ALTER TABLE `tipodeusuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT de la tabla `carreras`
--
ALTER TABLE `carreras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `fotesh`
--
ALTER TABLE `fotesh`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `grupos`
--
ALTER TABLE `grupos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1261;

--
-- AUTO_INCREMENT de la tabla `jdivision`
--
ALTER TABLE `jdivision`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `pdfs`
--
ALTER TABLE `pdfs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `practicas`
--
ALTER TABLE `practicas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `tipodeusuarios`
--
ALTER TABLE `tipodeusuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  ADD CONSTRAINT `asignaciones_ibfk_1` FOREIGN KEY (`maestro_id`) REFERENCES `tipodeusuarios` (`id`),
  ADD CONSTRAINT `asignaciones_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`),
  ADD CONSTRAINT `asignaciones_ibfk_3` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`),
  ADD CONSTRAINT `asignaciones_ibfk_4` FOREIGN KEY (`grupo_id`) REFERENCES `grupos` (`id`);

--
-- Filtros para la tabla `fotesh`
--
ALTER TABLE `fotesh`
  ADD CONSTRAINT `fk_fotesh_maestro_id` FOREIGN KEY (`Maestro_id`) REFERENCES `tipodeusuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fotesh_ibfk_1` FOREIGN KEY (`Materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `grupos`
--
ALTER TABLE `grupos`
  ADD CONSTRAINT `fk_grupos_carrera` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`);

--
-- Filtros para la tabla `jdivision`
--
ALTER TABLE `jdivision`
  ADD CONSTRAINT `jdivision_ibfk_1` FOREIGN KEY (`jefe_id`) REFERENCES `tipodeusuarios` (`id`),
  ADD CONSTRAINT `jdivision_ibfk_2` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
