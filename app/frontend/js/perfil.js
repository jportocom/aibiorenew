document.addEventListener('DOMContentLoaded', async () => {
  if (!isLoggedIn()) { location.href = '/dev/medepeso/area-pessoal.html'; return; }

  // Carregar dados actuais
  const perfil = await apiCall('/user/profile');
  if (perfil) {
    document.getElementById('nome').value   = perfil.nome || '';
    document.getElementById('sexo').value   = perfil.sexo || 'M';
    document.getElementById('idade').value  = perfil.idade || '';
    document.getElementById('altura').value = perfil.altura || '';
    document.getElementById('peso').value   = perfil.peso || '';
    document.getElementById('atividade').value  = perfil.atividade || 'sedentario';
    document.getElementById('tipo_dieta').value = perfil.tipo_dieta || 'omnivoro';
    if (perfil.come_ovos === false) document.getElementById('come_ovos').checked = false;
  }

  document.getElementById('form-perfil').addEventListener('submit', async (e) => {
    e.preventDefault();
    const dados = {
      nome:       document.getElementById('nome').value,
      sexo:       document.getElementById('sexo').value,
      idade:      parseInt(document.getElementById('idade').value),
      altura:     parseFloat(document.getElementById('altura').value),
      peso:       parseFloat(document.getElementById('peso').value),
      atividade:  document.getElementById('atividade').value,
      tipo_dieta: document.getElementById('tipo_dieta').value,
      come_ovos:  document.getElementById('come_ovos').checked
    };

    const res = await apiCall('/user/profile', 'PUT', dados);
    if (res?.sucesso) {
      document.getElementById('msg').textContent = 'Perfil guardado com sucesso!';
      document.getElementById('msg').style.color = 'var(--verde)';
      setTimeout(() => location.href = 'dashboard.html', 1500);
    }
  });
});
