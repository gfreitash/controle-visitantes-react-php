CREATE TABLE IF NOT EXISTS `tb_funcao`
(
    `id`     smallint(6) NOT NULL AUTO_INCREMENT,
    `funcao` varchar(25) NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `tb_usuario`
(
    `id` smallint(6) NOT NULL AUTO_INCREMENT,
    `funcao` smallint(6) NOT NULL,
    `nome` varchar(75) NOT NULL,
    `email` varchar(50) NOT NULL,
    `senha` varchar(60) NOT NULL,
    `refresh_token` varchar(255) DEFAULT NULL,
    `modificou_senha` tinyint(1) DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    KEY `funcao` (`funcao`),
    CONSTRAINT `tb_usuario_ibfk_1` FOREIGN KEY (`funcao`) REFERENCES `tb_funcao` (`id`)
);

CREATE TABLE IF NOT EXISTS `tb_visitante`
(
    `id`              int(11)     NOT NULL AUTO_INCREMENT,
    `cpf`             varchar(11) NOT NULL,
    `nome`            varchar(75) NOT NULL,
    `data_nascimento` date        DEFAULT NULL,
    `foto`            mediumtext  DEFAULT NULL,
    `identidade`      varchar(25) DEFAULT NULL,
    `expedidor`       varchar(20) DEFAULT NULL,
    `cadastrado_em`   datetime    DEFAULT NULL,
    `cadastrado_por`  smallint(6) DEFAULT NULL,
    `modificado_em`   datetime    DEFAULT NULL,
    `modificado_por`  smallint(6) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `cpf` (`cpf`),
    KEY `cadastrado_por` (`cadastrado_por`),
    KEY `modificado_por` (`modificado_por`),
    CONSTRAINT `tb_visitante_ibfk_1` FOREIGN KEY (`cadastrado_por`) REFERENCES `tb_usuario` (`id`),
    CONSTRAINT `tb_visitante_ibfk_2` FOREIGN KEY (`modificado_por`) REFERENCES `tb_usuario` (`id`)
);

CREATE TABLE IF NOT EXISTS `tb_visita`
(
    `id`             int(11)     NOT NULL AUTO_INCREMENT,
    `visitante_id`   int(11)     NOT NULL,
    `sala_visita`    varchar(50) NOT NULL,
    `motivo_visita`  varchar(255) DEFAULT NULL,
    `foi_liberado`   tinyint(1)  NOT NULL,
    `data_visita`    datetime    NOT NULL,
    `cadastrada_por` smallint(6) NOT NULL,
    `modificada_em`  datetime     DEFAULT NULL,
    `modificada_por` smallint(6)  DEFAULT NULL,
    `finalizada_em`  datetime     DEFAULT NULL,
    `finalizada_por` smallint(6)  DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `tb_visita_unique` (`id`, `visitante_id`),
    KEY `cadastrada_por` (`cadastrada_por`),
    KEY `modificada_por` (`modificada_por`),
    KEY `finalizada_por` (`finalizada_por`),
    KEY `visitante_id` (`visitante_id`),
    CONSTRAINT `tb_visita_ibfk_1` FOREIGN KEY (`cadastrada_por`) REFERENCES `tb_usuario` (`id`),
    CONSTRAINT `tb_visita_ibfk_2` FOREIGN KEY (`modificada_por`) REFERENCES `tb_usuario` (`id`),
    CONSTRAINT `tb_visita_ibfk_3` FOREIGN KEY (`finalizada_por`) REFERENCES `tb_usuario` (`id`),
    CONSTRAINT `tb_visita_ibfk_4` FOREIGN KEY (`visitante_id`) REFERENCES `tb_visitante` (`id`)
);

CREATE TABLE IF NOT EXISTS `tb_observacao`
(
    `id`          int(11)     NOT NULL AUTO_INCREMENT,
    `visita_id`   int(11)     NOT NULL,
    `observacao`  varchar(255) NOT NULL,
    `adicionada_em` datetime    NOT NULL,
    `adicionada_por` smallint(6) NOT NULL,
    `modificada_em` datetime     DEFAULT NULL,
    `modificada_por` smallint(6)  DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `cadastrada_por` (`adicionada_por`),
    KEY `modificada_por` (`modificada_por`),
    KEY `visita_id` (`visita_id`),
    CONSTRAINT `tb_observacao_ibfk_1` FOREIGN KEY (`adicionada_por`) REFERENCES `tb_usuario` (`id`),
    CONSTRAINT `tb_observacao_ibfk_2` FOREIGN KEY (`modificada_por`) REFERENCES `tb_usuario` (`id`),
    CONSTRAINT `tb_observacao_ibfk_3` FOREIGN KEY (`visita_id`) REFERENCES `tb_visita` (`id`)
);

INSERT INTO tb_funcao (id, funcao) VALUES (1, 'ADMINISTRADOR');
INSERT INTO tb_funcao (id, funcao) VALUES (2, 'USUARIO');

/* Usuário administrador com senha padrão 123 */
INSERT INTO tb_usuario (funcao, nome, email, senha) VALUES (1, 'ADMINISTRADOR', 'adm@adm.com', '$2y$13$oO4CWt40erbWk9xvqFACjO228OJ2gI4g1EIHDkizuRXQOPSvYZbGO');
