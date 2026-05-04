exports.gerarPlano = (tmb, user) => {
  // Distribuição calórica: 45% pequeno-almoço, 35% almoço, máx 400 jantar
  const pequeno_almoco = Math.round(tmb * 0.45);
  const almoco = Math.round(tmb * 0.35);
  const jantar = Math.min(400, Math.round(tmb * 0.20));

  // Calorias gastas em 2x caminhadas de 15 min
  // ~4.5 kcal/min ajustado ao peso do utilizador
  const pesoKg = user?.peso || 70;
  const fatorPeso = pesoKg / 70;
  const calorias_caminhada = Math.round(2 * 15 * 4.5 * fatorPeso);

  const deficit_previsto = calorias_caminhada; // só caminhada base

  return {
    pequeno_almoco,
    almoco,
    jantar,
    total_calorias: pequeno_almoco + almoco + jantar,
    caminhada_manha_min: 15,
    caminhada_tarde_min: 15,
    calorias_caminhada,
    deficit_previsto,
    hora_jantar: '19:00',
    nota: `Pequeno-almoço: ${pequeno_almoco} kcal | Almoço: ${almoco} kcal | Jantar: ${jantar} kcal`
  };
};
