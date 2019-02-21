INSERT INTO `cb_labels` (
    `id`,
    `title`,
    `ascendants_id`,
    `slug`,
    `status`
) VALUES (
    1,
    'Legislação',
    null,
    'legislacao',
    1
), (
    2,
    'Decreto',
    '[1]',
    'decreto',
    2
), (
    3,
    'Projeto de lei',
    '[1]',
    'projeto-de-lei',
    3
), (
    4,
    'Relatórios',
    '[1]',
    'relatorios',
    4
), (
    5,
    'RREO',
    '[1,4]',
    'rreo',
    5
), (
    6,
    'RGF',
    '[1,4]',
    'rgf',
    3
);
