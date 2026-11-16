-- Importe este arquivo selecionando primeiro o banco if0_..._safeflow no phpMyAdmin.
-- O script não cria nem seleciona bancos, pois hospedagens compartilhadas restringem isso.

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  perfil VARCHAR(50) NOT NULL DEFAULT 'Administrador',
  status ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
  phone VARCHAR(50) NULL,
  avatar VARCHAR(255) NULL,
  reset_token VARCHAR(64) NULL,
  reset_expira DATETIME NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS funcionarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  cargo VARCHAR(120) NOT NULL,
  setor VARCHAR(80) NOT NULL,
  matricula VARCHAR(50) NOT NULL UNIQUE,
  status ENUM('Ativo','Férias','Inativo') NOT NULL DEFAULT 'Ativo',
  epis_atribuidos INT NOT NULL DEFAULT 0,
  ca_vencendo VARCHAR(20) DEFAULT '-',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS epis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  ca VARCHAR(30) NOT NULL,
  categoria VARCHAR(80) NOT NULL,
  estoque INT NOT NULL DEFAULT 0,
  estoque_minimo INT NOT NULL DEFAULT 0,
  validade_ca DATE NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fornecedores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  cnpj VARCHAR(30) NULL,
  email VARCHAR(150) NULL,
  telefone VARCHAR(30) NULL,
  status ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  numero VARCHAR(30) NOT NULL UNIQUE,
  epi_id INT NULL,
  fornecedor_id INT NULL,
  quantidade INT NOT NULL DEFAULT 1,
  valor DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  data_pedido DATE NOT NULL,
  prazo DATE NULL,
  status ENUM('Aguardando','Aprovado','Recebido','Cancelado') NOT NULL DEFAULT 'Aguardando',
  observacoes TEXT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pedidos_epi FOREIGN KEY (epi_id) REFERENCES epis(id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pedidos_fornecedor FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS documentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  funcionario_id INT NULL,
  tipo VARCHAR(100) NOT NULL,
  arquivo VARCHAR(255) NOT NULL,
  paginas INT NOT NULL DEFAULT 1,
  status VARCHAR(50) NOT NULL DEFAULT 'Válido',
  data_documento DATE NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_documentos_funcionario FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS solicitacoes_orcamento (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  empresa VARCHAR(150) NOT NULL,
  telefone VARCHAR(30) NOT NULL,
  documento VARCHAR(30) NOT NULL,
  interesse TEXT NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS suppliers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  contact VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS supplier_prices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  supplier_id INT NOT NULL,
  product_id INT NOT NULL,
  price DECIMAL(12,2) NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_supplier_prices_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
  CONSTRAINT fk_supplier_prices_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados atualmente cadastrados no banco safeflow
INSERT IGNORE INTO `epis` VALUES
(1,'Capacete de Segurança Classe B','29638','Cabeça',42,20,'2027-09-15','2026-09-11 01:46:40'),
(2,'Óculos de Segurança Incolor','10346','Olhos/Face',86,30,'2027-03-21','2026-09-11 01:46:40'),
(3,'Protetor Auditivo Plug','5745','Audição',18,25,'2026-12-08','2026-09-11 01:46:40'),
(4,'Respirador PFF2','8357','Respiratório',64,40,'2027-05-14','2026-09-11 01:46:40'),
(5,'Luva de Vaqueta','15218','Mãos/Braços',12,25,'2026-10-30','2026-09-11 01:46:40'),
(6,'Luva Nitrílica','32041','Mãos/Braços',70,35,'2027-08-17','2026-09-11 01:46:40'),
(7,'Avental de Raspa','16890','Tronco',31,15,'2027-01-22','2026-09-11 01:46:40'),
(8,'Botina de Segurança','28914','Pés/Pernas',24,30,'2026-11-19','2026-09-11 01:46:40'),
(9,'Cinturão paraquedista','43872','Quedas',9,10,'2027-02-02','2026-09-11 01:46:40'),
(10,'Protetor Facial','22107','Olhos/Face',22,12,'2026-09-06','2026-09-11 01:46:40');

INSERT IGNORE INTO `fornecedores` VALUES
(1,'Protec Equipamentos','12.345.678/0001-90','contato@protec.com.br','(11) 3333-1020','Ativo','2026-09-11 01:46:41'),
(2,'SeguraMais EPI','23.456.789/0001-12','vendas@seguramais.com.br','(11) 3222-4411','Ativo','2026-09-11 01:46:41'),
(3,'Industrial Supply','45.987.321/0001-44','comercial@industrialsupply.com.br','(19) 3555-0088','Ativo','2026-09-11 01:46:41');

INSERT IGNORE INTO `funcionarios` VALUES
(1,'Ana Paula Souza','Operadora de Produção','Produção','MAT-00182','Ativo',5,'12/2026','2026-09-11 01:46:40'),
(2,'Bruno Henrique Lima','Técnico de Manutenção','Manutenção','MAT-00214','Ativo',7,'09/2026','2026-09-11 01:46:40'),
(3,'Camila Ferreira','Analista de Logística','Logística','MAT-00307','Ativo',4,'11/2026','2026-09-11 01:46:40'),
(4,'Diego Martins','Técnico de Segurança','SESMT','MAT-00119','Ativo',6,'08/2026','2026-09-11 01:46:40'),
(5,'Eduardo Santos','Operador de Empilhadeira','Logística','MAT-00266','Férias',3,'10/2026','2026-09-11 01:46:40'),
(6,'Fernanda Alves','Supervisora de Produção','Produção','MAT-00144','Ativo',8,'07/2026','2026-09-11 01:46:40'),
(7,'Gabriel Oliveira','Mecânico Industrial','Manutenção','MAT-00298','Ativo',6,'09/2026','2026-09-11 01:46:40'),
(8,'Juliana Costa','Engenheira de Segurança','SESMT','MAT-00087','Ativo',5,'12/2026','2026-09-11 01:46:40');

INSERT IGNORE INTO `pedidos` VALUES
(1,'PC-2026-081',5,1,100,4850.00,'2026-08-21','2026-09-02','Aguardando',NULL,'2026-09-11 01:46:41'),
(2,'PC-2026-078',8,2,50,6750.00,'2026-08-17','2026-08-29','Aprovado',NULL,'2026-09-11 01:46:41'),
(3,'PC-2026-074',3,3,80,1840.00,'2026-08-12','2026-08-25','Recebido',NULL,'2026-09-11 01:46:41');

INSERT IGNORE INTO `usuarios` VALUES
(1,'Marcos Neves','admin@safeflow.com.br','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','Administrador','Ativo','',NULL,NULL,NULL,'2026-09-11 01:46:38');
