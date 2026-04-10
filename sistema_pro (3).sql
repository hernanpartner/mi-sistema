-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-04-2026 a las 19:30:08
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
-- Base de datos: `sistema_pro`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`) VALUES
(1, 'AEREAS'),
(2, 'MARITIMAS'),
(3, 'TERRESTRES'),
(4, 'SEGURO'),
(5, 'OTRO SERVICIO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_contenedor`
--

CREATE TABLE `configuracion_contenedor` (
  `id` int(11) NOT NULL,
  `proyecto_id` int(11) DEFAULT NULL,
  `largo` float DEFAULT NULL,
  `ancho` float DEFAULT NULL,
  `alto` float DEFAULT NULL,
  `peso_max` float DEFAULT NULL,
  `apilado` tinyint(4) DEFAULT NULL,
  `rotacion` tinyint(4) DEFAULT NULL,
  `modo` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cubicaje`
--

CREATE TABLE `cubicaje` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `largo` float DEFAULT NULL,
  `ancho` float DEFAULT NULL,
  `alto` float DEFAULT NULL,
  `volumen` decimal(15,2) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `cantidad` int(11) DEFAULT 1,
  `peso` decimal(10,2) DEFAULT NULL,
  `peso_total` decimal(10,2) DEFAULT NULL,
  `proyecto_id` int(11) DEFAULT NULL,
  `apilable` tinyint(4) DEFAULT 1,
  `rotable` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial`
--

CREATE TABLE `historial` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `tarea_id` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `servicio_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_tareas`
--

CREATE TABLE `historial_tareas` (
  `id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `accion` varchar(50) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `servicio_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_tareas`
--

INSERT INTO `historial_tareas` (`id`, `tarea_id`, `accion`, `usuario_id`, `fecha`, `servicio_id`) VALUES
(1, 1, 'armin creó la tarea: 1 y la asignó a armin', 2, '2026-04-09 13:07:09', 1),
(2, 1, 'armin actualizó la descripción: 2', 2, '2026-04-09 13:07:24', 1),
(3, 1, 'armin cambió el estado a BLOQUEADO', 2, '2026-04-09 13:07:27', 1),
(4, 1, 'armin cambió el estado a EN PROCESO', 2, '2026-04-09 13:07:29', 1),
(5, 1, 'armin reasignó a monica, 3', 2, '2026-04-09 13:07:39', 1),
(6, 2, 'armin creó la tarea: a y la asignó a armin', 2, '2026-04-09 13:08:20', 1),
(7, 3, 'armin creó la tarea: qwe y la asignó a armin', 2, '2026-04-09 13:08:44', 1),
(8, 1, 'monica cambió el estado a TERMINADO', 3, '2026-04-09 13:09:38', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `leida` tinyint(4) DEFAULT 0,
  `fecha` datetime DEFAULT current_timestamp(),
  `leido` tinyint(1) DEFAULT 0,
  `tarea_id` int(11) DEFAULT NULL,
  `servicio_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_id`, `mensaje`, `leida`, `fecha`, `leido`, `tarea_id`, `servicio_id`) VALUES
(1, 2, 'armin te asignó una nueva tarea: GUIA', 0, '2026-04-09 13:07:09', 1, 1, 1),
(2, 2, 'armin te asignó la tarea: GUIA', 0, '2026-04-09 13:07:24', 1, 1, 1),
(3, 3, 'armin te asignó la tarea: GUIA', 0, '2026-04-09 13:07:39', 1, 1, 1),
(4, 2, 'armin te asignó una nueva tarea: MIC1', 0, '2026-04-09 13:08:20', 1, 2, 1),
(5, 2, 'armin te asignó una nueva tarea: MIC2', 0, '2026-04-09 13:08:44', 1, 3, 1),
(6, 2, 'monica cambió la tarea \'GUIA\' a TERMINADO', 0, '2026-04-09 13:09:38', 0, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int(11) NOT NULL,
  `rol` varchar(50) DEFAULT NULL,
  `permiso` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id`, `rol`, `permiso`) VALUES
(217, 'USER', 'servicios.ver'),
(218, 'USER', 'servicios.crear'),
(219, 'USER', 'servicios.editar'),
(220, 'USER', 'servicios.eliminar'),
(221, 'USER', 'tareas.ver'),
(222, 'USER', 'tareas.crear'),
(223, 'USER', 'tareas.cambiar_estado'),
(224, 'USER', 'tareas.historial'),
(225, 'USER', 'historial.ver'),
(226, 'USER', 'usuarios.ver'),
(227, 'USER', 'usuarios.crear'),
(228, 'USER', 'usuarios.editar'),
(229, 'USER', 'usuarios.eliminar'),
(230, 'USER', 'export.ver');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyectos`
--

CREATE TABLE `proyectos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `cliente` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `categoria_id` int(11) DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'activo',
  `master` varchar(100) DEFAULT NULL,
  `house` varchar(100) DEFAULT NULL,
  `origen` varchar(100) DEFAULT NULL,
  `destino` varchar(100) DEFAULT NULL,
  `factura` varchar(100) DEFAULT NULL,
  `etd` datetime DEFAULT NULL,
  `eta` datetime DEFAULT NULL,
  `estado_logistico` enum('programado','en_transito','arribado') DEFAULT 'programado',
  `visible_pantalla` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `codigo`, `cliente`, `descripcion`, `creado_por`, `fecha_creacion`, `categoria_id`, `estado`, `master`, `house`, `origen`, `destino`, `factura`, `etd`, `eta`, `estado_logistico`, `visible_pantalla`) VALUES
(1, 'PIA01', 'ABEN', NULL, NULL, '2026-04-09 17:06:49', 2, 'activo', NULL, NULL, 'china', 'la paz', NULL, '2026-04-10 13:06:00', '2026-04-10 13:06:00', 'programado', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id` int(11) NOT NULL,
  `servicio_id` int(11) DEFAULT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `responsable_id` int(11) DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'PENDIENTE',
  `fecha_limite` date DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `asignado_por` int(11) DEFAULT NULL,
  `prioridad` varchar(20) DEFAULT 'MEDIA',
  `fecha_finalizado` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id`, `servicio_id`, `titulo`, `descripcion`, `responsable_id`, `estado`, `fecha_limite`, `fecha_creacion`, `asignado_por`, `prioridad`, `fecha_finalizado`) VALUES
(1, 1, 'GUIA', '3', 3, 'TERMINADO', '2026-04-10', '2026-04-09 17:07:09', 2, 'BAJA', '2026-04-09 13:09:38'),
(2, 1, 'MIC1', 'a', 2, 'PENDIENTE', '2026-04-10', '2026-04-09 17:08:20', 2, 'BAJA', NULL),
(3, 1, 'MIC2', 'qwe', 2, 'PENDIENTE', '2026-04-10', '2026-04-09 17:08:44', 2, 'BAJA', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `rol` varchar(20) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `intentos` int(11) DEFAULT 0,
  `bloqueado_hasta` datetime DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `usuario`, `password`, `rol`, `fecha_creacion`, `intentos`, `bloqueado_hasta`, `remember_token`) VALUES
(1, 'Administrador', 'admin', '$2y$10$DUo0gPgSm7ebb9qjejTSHeDlsj9bwIPD.1PNb/5xV.LFHf.ikNA9m', 'ADMIN', '2026-04-09 17:05:27', 0, NULL, NULL),
(2, 'armin', 'armin', '$2y$10$fXEnDyyzGsbX2jPzjT1E1.UWf5WLYDImMPcQrEobodUCCxtLANMqG', 'USER', '2026-04-09 17:06:02', 0, NULL, NULL),
(3, 'monica', 'monica', '$2y$10$Dn09TJJjvZvwyH72T1kDqeX2/UIHuBeMXOyNnh5qS1.KrxDo3cwS2', 'USER', '2026-04-09 17:06:09', 0, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion_contenedor`
--
ALTER TABLE `configuracion_contenedor`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cubicaje`
--
ALTER TABLE `cubicaje`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `historial`
--
ALTER TABLE `historial`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `historial_tareas`
--
ALTER TABLE `historial_tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tarea_id` (`tarea_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_categoria` (`categoria_id`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `configuracion_contenedor`
--
ALTER TABLE `configuracion_contenedor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cubicaje`
--
ALTER TABLE `cubicaje`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial`
--
ALTER TABLE `historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_tareas`
--
ALTER TABLE `historial_tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=231;

--
-- AUTO_INCREMENT de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `historial_tareas`
--
ALTER TABLE `historial_tareas`
  ADD CONSTRAINT `historial_tareas_ibfk_1` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD CONSTRAINT `fk_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
