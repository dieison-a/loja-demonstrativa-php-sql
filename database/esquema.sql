CREATE TABLE categoria (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(80) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_categoria_nome (nome)
);

CREATE TABLE produto (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    categoria_id INT UNSIGNED NOT NULL,
    nome VARCHAR(120) NOT NULL,
    preco_centavos INT UNSIGNED NOT NULL,
    estoque INT UNSIGNED NOT NULL DEFAULT 0,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (id),
    KEY idx_produto_categoria (categoria_id),
    CONSTRAINT fk_produto_categoria
        FOREIGN KEY (categoria_id) REFERENCES categoria (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_produto_preco CHECK (preco_centavos > 0)
);

CREATE TABLE pedido (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    situacao VARCHAR(20) NOT NULL DEFAULT 'aberto',
    PRIMARY KEY (id),
    CONSTRAINT chk_pedido_situacao
        CHECK (situacao IN ('aberto', 'concluido', 'cancelado'))
);

CREATE TABLE item_pedido (
    pedido_id BIGINT UNSIGNED NOT NULL,
    produto_id INT UNSIGNED NOT NULL,
    quantidade SMALLINT UNSIGNED NOT NULL,
    preco_unitario_centavos INT UNSIGNED NOT NULL,
    PRIMARY KEY (pedido_id, produto_id),
    CONSTRAINT fk_item_pedido_pedido
        FOREIGN KEY (pedido_id) REFERENCES pedido (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_item_pedido_produto
        FOREIGN KEY (produto_id) REFERENCES produto (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_item_quantidade CHECK (quantidade > 0)
);

INSERT INTO categoria (nome) VALUES
    ('Mercearia'),
    ('Papelaria'),
    ('Utilidades');

INSERT INTO produto (categoria_id, nome, preco_centavos, estoque) VALUES
    (1, 'Café da serra', 1890, 8),
    (2, 'Caderno pontilhado', 2450, 12),
    (3, 'Garrafa térmica', 7900, 5);
