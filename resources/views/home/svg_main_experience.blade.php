<canvas id="rotate_cube"></canvas>
<script>
const canvas = document.getElementById('rotate_cube');
const ctx = canvas.getContext('2d');

let SIZE, CX, CY, CUBE_SIZE, SPHERE_R;

function setupSize() {
  SIZE = Math.min(window.innerWidth, window.innerHeight);
  canvas.width  = SIZE;
  canvas.height = SIZE;
  CX        = SIZE / 2;
  CY        = SIZE / 2;
  // 왜곡이 없으면 큐브가 작아 보일 수 있어 크기를 살짝 키웠습니다.
  CUBE_SIZE = SIZE * 0.32; 
  SPHERE_R  = SIZE * 0.0035; 
}
setupSize();

// ─── 3x3x3 격자 데이터 생성 ───────────────────────────
const G = [-1, -1/3, 1/3, 1]; 
const vertices = [];
const edges = [];

for (let i = 0; i < 4; i++) {
  for (let j = 0; j < 4; j++) {
    for (let k = 0; k < 4; k++) {
      vertices.push([G[i], G[j], G[k]]);
    }
  }
}

const getIdx = (i, j, k) => i * 16 + j * 4 + k;

for (let i = 0; i < 4; i++) {
  for (let j = 0; j < 4; j++) {
    for (let k = 0; k < 4; k++) {
      if (i < 3) edges.push([getIdx(i, j, k), getIdx(i + 1, j, k)]);
      if (j < 3) edges.push([getIdx(i, j, k), getIdx(i, j + 1, k)]);
      if (k < 3) edges.push([getIdx(i, j, k), getIdx(i, j, k + 1)]);
    }
  }
}

// ─── 회전 함수 ──────────────────────────────────────────
function rotX([x, y, z], a) {
  let c = Math.cos(a), s = Math.sin(a);
  return [x, y*c - z*s, y*s + z*c];
}
function rotY([x, y, z], a) {
  let c = Math.cos(a), s = Math.sin(a);
  return [x*c + z*s, y, -x*s + z*c];
}
function rotZ([x, y, z], a) {
  let c = Math.cos(a), s = Math.sin(a);
  return [x*c - y*s, x*s + y*c, z];
}

// ─── 투영 함수 (왜곡 최소화) ─────────────────────────────
function project([x, y, z]) {
  // s(scale factor)를 거의 1에 수렴하게 만들어 원근 왜곡을 제거합니다.
  // z값에 아주 미세한 가중치(0.05)만 주어 입체적인 느낌만 살짝 남깁니다.
  const s = 1 / (1 + z * 0.05); 
  return [CX + x * CUBE_SIZE * s, CY + y * CUBE_SIZE * s];
}

// ─── 애니메이션 ───────────────────────────────────────────
let aX = 0, aY = 0, aZ = 0;

function draw() {
  ctx.fillStyle = '#000';
  ctx.fillRect(0, 0, SIZE, SIZE);

  const transformed = vertices.map(v => {
    let p = rotX(v, aX);
    p = rotY(p, aY);
    p = rotZ(p, aZ);
    return p;
  });
  const projected = transformed.map(project);

  // 1. 엣지 그리기
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.35)';
  ctx.lineWidth   = SIZE * 0.0012;

  edges.forEach(([a, b]) => {
    const [ax, ay] = projected[a];
    const [bx, by] = projected[b];
    ctx.beginPath();
    ctx.moveTo(ax, ay);
    ctx.lineTo(bx, by);
    ctx.stroke();
  });

  // 2. 점 그리기
  ctx.fillStyle = '#ffffff';
  projected.forEach(([px, py]) => {
    ctx.beginPath();
    ctx.arc(px, py, SPHERE_R, 0, Math.PI * 2);
    ctx.fill();
  });

  aX += 0.004;
  aY += 0.006;
  aZ += 0.003;

  requestAnimationFrame(draw);
}

draw();
window.addEventListener('resize', () => { setupSize(); });
</script>