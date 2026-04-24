CREATE DATABASE projetofilmes;
USE projetofilmes;

CREATE TABLE filmes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    diretor VARCHAR(100) NOT NULL,
    ano INT NOT NULL,
    genero VARCHAR(50) NOT NULL,
    imagem VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_usuario VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil VARCHAR(20) NOT NULL DEFAULT 'usuario',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO filmes (titulo, diretor, ano, genero, imagem) VALUES
('Interestelar', 'Christopher Nolan', 2014, 'Ficção Científica', 'https://upload.wikimedia.org/wikipedia/pt/3/3a/Interstellar_Filme.png'),
('Vingadores: Ultimato', 'Anthony Russo', 2019, 'Ação', 'https://upload.wikimedia.org/wikipedia/pt/thumb/9/9b/Avengers_Endgame.jpg/250px-Avengers_Endgame.jpg'),
('Parasita', 'Bong Joon-ho', 2019, 'Drama', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQZrXu26D9I61d5cdzolbda9JvpB-zpcNmVzQ&s'),
('O Poderoso Chefão', 'Francis Ford Coppola', 1972, 'Crime', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSv_Il14iCiBPkv-AG1qOnaKwv4DV6csx5eww&s');

INSERT INTO usuarios (nome_usuario, email, senha, perfil)
VALUES ('admin', 'admin@email.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'admin');

-- admin@email.com 
-- admin123