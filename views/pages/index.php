<div class="container py-5" style="background: linear-gradient(135deg, #ffffff 0%, #00529F 100%); min-height: 100vh;">
  <div class="row mb-4">
    <div class="col text-center">
      <img src="https://upload.wikimedia.org/wikipedia/en/5/56/Real_Madrid_CF.svg" alt="Real Madrid Logo" width="120">
      <h1 class="display-4 mt-3" style="color: #FFD700; font-weight: bold; text-shadow: 2px 2px #00529F;">¡Bienvenido a la Casa Blanca!</h1>
      <p class="lead" style="color: #00529F;">¡Hala Madrid y nada más! Vive la pasión, la historia y la grandeza del mejor club del mundo.</p>
      <button id="halaBtn" class="btn btn-warning btn-lg mt-3" style="color: #00529F; font-weight: bold;">¡Hala Madrid!</button>
    </div>
  </div>
  <div class="row justify-content-center mb-5">
    <div class="col-lg-6 text-center">
      <img src="https://www.realmadrid.com/img/horizontal_940px/estadio-santiago-bernabeu_20230915095632.jpg" class="img-fluid rounded shadow" alt="Estadio Santiago Bernabéu">
      <p class="mt-3" style="color: #00529F;">Descubre el legendario Estadio Santiago Bernabéu, donde la magia sucede.</p>
    </div>
  </div>
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-lg border-0">
        <div class="card-body text-center">
          <h2 class="card-title" style="color: #00529F;">¿Sabías que...?</h2>
          <p class="card-text" id="fact" style="font-size: 1.2rem; color: #333;">Real Madrid ha ganado más de 30 Ligas y 14 Champions League.</p>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
const facts = [
  "Real Madrid fue fundado en 1902 y es considerado el mejor club del siglo XX.",
  "El Santiago Bernabéu es uno de los estadios más emblemáticos del mundo.",
  "Cristiano Ronaldo es el máximo goleador histórico del club.",
  "Real Madrid ha ganado 14 Champions League, más que ningún otro club.",
  "La afición del Real Madrid es conocida como 'Madridistas'.",
  "El club tiene más de 30 títulos de LaLiga.",
  "Su himno es uno de los más reconocidos en el fútbol mundial.",
  "El clásico contra el FC Barcelona es uno de los partidos más vistos del planeta."
];
document.getElementById('halaBtn').addEventListener('click', function() {
  const fact = facts[Math.floor(Math.random() * facts.length)];
  document.getElementById('fact').textContent = fact;
  this.textContent = "¡Vamos Madrid!";
  this.classList.add('pulse');
  setTimeout(() => this.classList.remove('pulse'), 600);
});
// Animación simple para el botón
const style = document.createElement('style');
style.innerHTML = `
  .pulse {
    animation: pulse 0.6s;
  }
  @keyframes pulse {
    0% { transform: scale(1);}
    50% { transform: scale(1.15);}
    100% { transform: scale(1);}
  }
`;
document.head.appendChild(style);
</script>