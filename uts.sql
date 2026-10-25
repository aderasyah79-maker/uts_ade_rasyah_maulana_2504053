create table petugas(
    id_petugas int primary key not null,
    nama_petugas varchar(100) not null,
    alamat text not null,
    jenis_kelamin int(1) not null,
    no_telp int not null
);

create table akun_petugas(
    username varchar(10) primary key not null,
    id_petugas int not null,
    password varchar(50) not null,
    constraint fk_petugas_akun_petugas foreign key(id_petugas) references petugas(id_petugas)
);

insert into petugas
values(2001, 'Ade Rasyah Maulana', 'jl.ciracas no.38', 1, 089605180051),
(2002, 'Muhammad Dien Muharam', 'jl.benggala no.39', 1, 083873627237);

insert into akun_petugas
values('Rasyah38', 2001, md5('AdeRasyah21')),
('Dien39', 2002, md5('DienMuharam22'));

create table admin(
    id_admin int primary key not null,
    nama_admin varchar(100) not null,
    alamat text not null,
    jenis_kelamin int(1) not null,
    no_telp int not null
);

create table akun_admin(
    username varchar(10) primary key not null,
    id_admin int not null,
    password varchar(50) not null,
    constraint fk_petugas_akun_admin foreign key(id_admin) references admin(id_admin)
);

insert into admin
values(1001, 'Muhammad Akmal', 'jl.ciracas no.15', 1, 087893246123),
(1002, 'Keysha Awal Ramdhanie', 'jl.benggala no.16', 0, 089625641645);

insert into akun_admin
values('Akmal15', 1001, md5('Akmal10')),
('Keysha16', 1002, md5('KeyshaAwal12'));

create table lokasi(
    id_lokasi int not null AUTO_INCREMENT primary key,
    nama_lokasi varchar(50) not null,
    lokasi varchar(100) not null
);

create table stasiun(
    id_stasiun int not null AUTO_INCREMENT primary key,
    nama_stasiun varchar(50) not null,
    id_lokasi int not null,
    stastus varchar(10) not null,
    constraint fk_stasiun_lokasi foreign key(id_lokasi) references lokasi(id_lokasi)
);

create table pengukuran(
    id_pengukuran int not null primary key,
    id_stasiun int not null,
    id_admin int not null,
    id_petugas int not null,
    tinggi DECIMAL not null,
    periode DECIMAL not null,
    suhu DECIMAL not null,
    waktu TIMESTAMP not null,
    constraint fk_pengukuran_stasiun foreign key(id_stasiun) REFERENCES stasiun(id_stasiun),
    constraint fk_pengukuran_admin foreign key(id_admin) REFERENCES admin(id_admin),
    constraint fk_pengukuran_petugas foreign key(id_petugas) REFERENCES petugas(id_petugas)
);

INSERT INTO lokasi VALUES
('','Anyer', '-6.0533, 105.9227'),
('','Pelabuhan Merak', '-5.929750, 105.994333'),
('','Labuan', '-6.376974, 105.828506');

INSERT INTO stasiun
VALUES(2010, 'Stasiun Anyer', 1, 'Aktif'),
(2011, 'Stasiun Merak', 2, 'Aktif'),
(2012, 'Stasiun Labuan', 3, 'Aktif');


INSERT INTO pengukuran
VALUES
(1, 2010, 1001, 2001, 1.25, 5.40, 29.10, '2026-10-25 08:00:00'),
(2, 2011, 1002, 2002, 1.60, 6.20, 29.50, '2026-10-25 08:05:00'),
(3, 2012, 1001, 2001, 0.95, 4.80, 28.70, '2026-10-25 08:10:00'),
(4, 2010, 1002, 2002, 1.40, 5.80, 29.30, '2026-10-25 08:15:00'),
(5, 2011, 1001, 2001, 1.75, 6.50, 29.80, '2026-10-25 08:20:00'),
(6, 2012, 1002, 2002, 1.10, 5.10, 28.90, '2026-10-25 08:25:00'),
(7, 2010, 1001, 2002, 1.55, 6.00, 29.40, '2026-10-25 08:30:00'),
(8, 2011, 1002, 2001, 1.30, 5.60, 29.20, '2026-10-25 08:35:00'),
(9, 2012, 1001, 2002, 0.85, 4.50, 28.50, '2026-10-25 08:40:00');
