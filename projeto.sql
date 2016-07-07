-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 06, 2016 at 01:53 PM
-- Server version: 5.7.12-0ubuntu1.1
-- PHP Version: 7.0.4-7ubuntu2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `projeto`
--

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `levenshtein` (`s1` VARCHAR(255) CHARACTER SET utf8, `s2` VARCHAR(255) CHARACTER SET utf8) RETURNS TINYINT(3) UNSIGNED NO SQL
    DETERMINISTIC
BEGIN
	DECLARE s1_len, s2_len, i, j, c, c_temp TINYINT UNSIGNED;
		DECLARE cv0, cv1 VARBINARY(256);
	
			IF (s1 + s2) IS NULL THEN
		RETURN NULL;
	END IF;
	
	SET s1_len = CHAR_LENGTH(s1),
		s2_len = CHAR_LENGTH(s2),
		cv1 = 0x00,
		j = 1,
		i = 1,
		c = 0;
	
			IF (s1 = s2) THEN
		RETURN 0;
	ELSEIF (s1_len = 0) THEN
		RETURN s2_len;
	ELSEIF (s2_len = 0) THEN
		RETURN s1_len;
	END IF;
	
	WHILE (j <= s2_len) DO
		SET cv1 = CONCAT(cv1, CHAR(j)),
		j = j + 1;
	END WHILE;
	
	WHILE (i <= s1_len) DO
		SET c = i,
			cv0 = CHAR(i),
			j = 1;
		
		WHILE (j <= s2_len) DO
			SET c = c + 1;
			
			SET c_temp = ORD(SUBSTRING(cv1, j, 1)) 				+ (NOT (SUBSTRING(s1, i, 1) = SUBSTRING(s2, j, 1))); 			IF (c > c_temp) THEN
				SET c = c_temp;
			END IF;
			
			SET c_temp = ORD(SUBSTRING(cv1, j+1, 1)) + 1;
			IF (c > c_temp) THEN
				SET c = c_temp;
			END IF;
			
			SET cv0 = CONCAT(cv0, CHAR(c)),
				j = j + 1;
		END WHILE;
		
		SET cv1 = cv0,
			i = i + 1;
	END WHILE;
	
	RETURN c;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `levenshtein_ratio` (`s1` VARCHAR(255) CHARSET utf8, `s2` VARCHAR(255) CHARSET utf8) RETURNS TINYINT(3) UNSIGNED NO SQL
    DETERMINISTIC
    COMMENT 'Levenshtein ratio between strings'
BEGIN
	DECLARE s1_len TINYINT UNSIGNED DEFAULT CHAR_LENGTH(s1);
	DECLARE s2_len TINYINT UNSIGNED DEFAULT CHAR_LENGTH(s2);
	RETURN ((levenshtein(s1, s2) / IF(s1_len > s2_len, s1_len, s2_len)) * 100);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `anoEleitoral`
--

CREATE TABLE `anoEleitoral` (
  `codigo` int(11) NOT NULL,
  `ano` varchar(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `candBens`
--

CREATE TABLE `candBens` (
  `codigo` int(11) NOT NULL,
  `id` varchar(15) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `valor` float NOT NULL,
  `tipo` int(11) NOT NULL,
  `descricao` varchar(200) NOT NULL,
  `linha` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `candCargo`
--

CREATE TABLE `candCargo` (
  `codigo` int(11) NOT NULL,
  `nome` varchar(120) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `candDespesas`
--

CREATE TABLE `candDespesas` (
  `codigo` int(11) NOT NULL,
  `id` varchar(15) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `valor` float NOT NULL,
  `tipo` int(11) NOT NULL,
  `descricao` varchar(200) NOT NULL,
  `linha` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `candEscolaridade`
--

CREATE TABLE `candEscolaridade` (
  `codigo` int(11) NOT NULL,
  `nome` varchar(120) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `candidato`
--

CREATE TABLE `candidato` (
  `codigo` int(11) NOT NULL,
  `id` varchar(15) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `cpf` varchar(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `profissao` int(5) NOT NULL,
  `partido` int(5) NOT NULL,
  `cargo` int(5) NOT NULL,
  `escolaridade` int(5) NOT NULL,
  `situacao` varchar(50) NOT NULL,
  `qdeSessoes` varchar(5) DEFAULT NULL,
  `qdeProcessos` varchar(5) DEFAULT NULL,
  `foto` mediumblob,
  `linha` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `candProfissao`
--

CREATE TABLE `candProfissao` (
  `codigo` int(11) NOT NULL,
  `nome` varchar(120) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `candReceitas`
--

CREATE TABLE `candReceitas` (
  `codigo` int(11) NOT NULL,
  `id` varchar(15) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `valor` float NOT NULL,
  `tipo` int(11) NOT NULL,
  `doador` int(11) NOT NULL,
  `linha` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `doador`
--

CREATE TABLE `doador` (
  `codigo` int(11) NOT NULL,
  `nome` varchar(300) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `estado`
--

CREATE TABLE `estado` (
  `codigo` int(5) NOT NULL,
  `nome` varchar(2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `partDespesas`
--

CREATE TABLE `partDespesas` (
  `codigo` int(11) NOT NULL,
  `partido` int(11) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `valor` float NOT NULL,
  `tipo` int(11) NOT NULL,
  `descricao` varchar(200) NOT NULL,
  `linhaPartido` int(11) DEFAULT NULL,
  `linhaComite` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `partido`
--

CREATE TABLE `partido` (
  `codigo` int(11) NOT NULL,
  `sigla` varchar(50) NOT NULL,
  `nome` varchar(120) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `partReceitas`
--

CREATE TABLE `partReceitas` (
  `codigo` int(11) NOT NULL,
  `partido` int(11) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `valor` float NOT NULL,
  `tipo` int(11) NOT NULL,
  `doador` int(11) NOT NULL,
  `linhaPartido` int(11) DEFAULT NULL,
  `linhaComite` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tipo`
--

CREATE TABLE `tipo` (
  `codigo` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `topBens`
--

CREATE TABLE `topBens` (
  `codigo` int(11) NOT NULL,
  `cpf` varchar(11) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `valor` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `topCandidatos`
--

CREATE TABLE `topCandidatos` (
  `codigo` int(11) NOT NULL,
  `cpf` varchar(11) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `valor` float NOT NULL,
  `tipo` varchar(10) NOT NULL,
  `estado` varchar(2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `topDoador`
--

CREATE TABLE `topDoador` (
  `codigo` int(11) NOT NULL,
  `doador` int(11) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `valor` float NOT NULL,
  `estado` varchar(2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `topPartidos`
--

CREATE TABLE `topPartidos` (
  `codigo` int(11) NOT NULL,
  `partido` int(11) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `valor` float NOT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `tipo` varchar(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `topRendas`
--

CREATE TABLE `topRendas` (
  `codigo` int(11) NOT NULL,
  `cpf` varchar(11) NOT NULL,
  `valor` float NOT NULL,
  `estado` varchar(2) NOT NULL,
  `periodo` varchar(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anoEleitoral`
--
ALTER TABLE `anoEleitoral`
  ADD PRIMARY KEY (`codigo`),
  ADD UNIQUE KEY `ano` (`ano`);

--
-- Indexes for table `candBens`
--
ALTER TABLE `candBens`
  ADD PRIMARY KEY (`codigo`);

--
-- Indexes for table `candCargo`
--
ALTER TABLE `candCargo`
  ADD PRIMARY KEY (`codigo`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Indexes for table `candDespesas`
--
ALTER TABLE `candDespesas`
  ADD PRIMARY KEY (`codigo`);

--
-- Indexes for table `candEscolaridade`
--
ALTER TABLE `candEscolaridade`
  ADD PRIMARY KEY (`codigo`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Indexes for table `candidato`
--
ALTER TABLE `candidato`
  ADD PRIMARY KEY (`codigo`),
  ADD KEY `nome` (`nome`);

--
-- Indexes for table `candProfissao`
--
ALTER TABLE `candProfissao`
  ADD PRIMARY KEY (`codigo`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Indexes for table `candReceitas`
--
ALTER TABLE `candReceitas`
  ADD PRIMARY KEY (`codigo`);

--
-- Indexes for table `doador`
--
ALTER TABLE `doador`
  ADD PRIMARY KEY (`codigo`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Indexes for table `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`codigo`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Indexes for table `partDespesas`
--
ALTER TABLE `partDespesas`
  ADD PRIMARY KEY (`codigo`);

--
-- Indexes for table `partido`
--
ALTER TABLE `partido`
  ADD PRIMARY KEY (`codigo`);

--
-- Indexes for table `partReceitas`
--
ALTER TABLE `partReceitas`
  ADD PRIMARY KEY (`codigo`);

--
-- Indexes for table `tipo`
--
ALTER TABLE `tipo`
  ADD PRIMARY KEY (`codigo`),
  ADD KEY `nome` (`nome`);

--
-- Indexes for table `topBens`
--
ALTER TABLE `topBens`
  ADD PRIMARY KEY (`codigo`),
  ADD UNIQUE KEY `cpf` (`cpf`,`ano`,`estado`);

--
-- Indexes for table `topCandidatos`
--
ALTER TABLE `topCandidatos`
  ADD PRIMARY KEY (`codigo`),
  ADD UNIQUE KEY `cpf` (`cpf`,`ano`,`valor`,`tipo`,`estado`);

--
-- Indexes for table `topDoador`
--
ALTER TABLE `topDoador`
  ADD PRIMARY KEY (`codigo`),
  ADD UNIQUE KEY `doador` (`doador`,`ano`,`valor`,`estado`);

--
-- Indexes for table `topPartidos`
--
ALTER TABLE `topPartidos`
  ADD PRIMARY KEY (`codigo`),
  ADD UNIQUE KEY `partido` (`partido`,`ano`,`valor`,`estado`,`tipo`);

--
-- Indexes for table `topRendas`
--
ALTER TABLE `topRendas`
  ADD PRIMARY KEY (`codigo`),
  ADD UNIQUE KEY `cpf` (`cpf`,`valor`,`estado`,`periodo`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anoEleitoral`
--
ALTER TABLE `anoEleitoral`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `candBens`
--
ALTER TABLE `candBens`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=888553;
--
-- AUTO_INCREMENT for table `candCargo`
--
ALTER TABLE `candCargo`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT for table `candDespesas`
--
ALTER TABLE `candDespesas`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5559207;
--
-- AUTO_INCREMENT for table `candEscolaridade`
--
ALTER TABLE `candEscolaridade`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `candidato`
--
ALTER TABLE `candidato`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=408686;
--
-- AUTO_INCREMENT for table `candProfissao`
--
ALTER TABLE `candProfissao`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=232;
--
-- AUTO_INCREMENT for table `candReceitas`
--
ALTER TABLE `candReceitas`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3832;
--
-- AUTO_INCREMENT for table `doador`
--
ALTER TABLE `doador`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=580;
--
-- AUTO_INCREMENT for table `estado`
--
ALTER TABLE `estado`
  MODIFY `codigo` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
--
-- AUTO_INCREMENT for table `partDespesas`
--
ALTER TABLE `partDespesas`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=486620;
--
-- AUTO_INCREMENT for table `partido`
--
ALTER TABLE `partido`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;
--
-- AUTO_INCREMENT for table `partReceitas`
--
ALTER TABLE `partReceitas`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tipo`
--
ALTER TABLE `tipo`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;
--
-- AUTO_INCREMENT for table `topBens`
--
ALTER TABLE `topBens`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6280;
--
-- AUTO_INCREMENT for table `topCandidatos`
--
ALTER TABLE `topCandidatos`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=321;
--
-- AUTO_INCREMENT for table `topDoador`
--
ALTER TABLE `topDoador`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;
--
-- AUTO_INCREMENT for table `topPartidos`
--
ALTER TABLE `topPartidos`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=274;
--
-- AUTO_INCREMENT for table `topRendas`
--
ALTER TABLE `topRendas`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
