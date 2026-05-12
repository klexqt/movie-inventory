// ===================== GALAXY BACKGROUND =====================
(function () {
    const canvas = document.getElementById('galaxy-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    let W = canvas.width  = window.innerWidth;
    let H = canvas.height = window.innerHeight;

    window.addEventListener('resize', () => {
        W = canvas.width  = window.innerWidth;
        H = canvas.height = window.innerHeight;
        initStars();
    });

    const STAR_COUNT   = 280;
    const SHOOT_COUNT  = 3;
    let   stars        = [];
    let   shooters     = [];

    function rand(min, max) { return Math.random() * (max - min) + min; }

    // --- Static / twinkling stars ---
    function initStars() {
        stars = [];
        for (let i = 0; i < STAR_COUNT; i++) {
            stars.push({
                x:       rand(0, W),
                y:       rand(0, H),
                r:       rand(0.3, 1.8),
                alpha:   rand(0.4, 1),
                dAlpha:  rand(0.003, 0.012) * (Math.random() < 0.5 ? 1 : -1),
                color:   randomStarColor(),
            });
        }
    }

    function randomStarColor() {
        const colors = ['255,255,255','200,220,255','255,220,180','180,200,255','255,180,180'];
        return colors[Math.floor(Math.random() * colors.length)];
    }

    // --- Shooting stars ---
    function newShooter() {
        const angle = rand(15, 45) * Math.PI / 180;
        const speed = rand(6, 14);
        return {
            x:      rand(0, W),
            y:      rand(0, H * 0.5),
            vx:     Math.cos(angle) * speed,
            vy:     Math.sin(angle) * speed,
            len:    rand(80, 160),
            alpha:  1,
            active: true,
        };
    }

    function initShooters() {
        shooters = [];
        for (let i = 0; i < SHOOT_COUNT; i++) {
            const s = newShooter();
            s.x = rand(0, W);
            s.y = rand(0, H);
            s.alpha = rand(0, 1);
            shooters.push(s);
        }
    }

    // --- Nebula blobs (static, drawn once) ---
    function drawNebula() {
        const blobs = [
            { x: W * 0.2,  y: H * 0.3,  r: 220, c: '60,20,120'   },
            { x: W * 0.75, y: H * 0.6,  r: 260, c: '10,40,100'   },
            { x: W * 0.5,  y: H * 0.15, r: 180, c: '80,10,60'    },
            { x: W * 0.9,  y: H * 0.1,  r: 150, c: '20,60,100'   },
        ];
        blobs.forEach(b => {
            const g = ctx.createRadialGradient(b.x, b.y, 0, b.x, b.y, b.r);
            g.addColorStop(0,   `rgba(${b.c},0.18)`);
            g.addColorStop(0.5, `rgba(${b.c},0.07)`);
            g.addColorStop(1,   `rgba(${b.c},0)`);
            ctx.fillStyle = g;
            ctx.beginPath();
            ctx.arc(b.x, b.y, b.r, 0, Math.PI * 2);
            ctx.fill();
        });
    }

    function draw() {
        ctx.clearRect(0, 0, W, H);

        // Deep space background
        ctx.fillStyle = '#0a0a0f';
        ctx.fillRect(0, 0, W, H);

        // Nebula
        drawNebula();

        // Twinkling stars
        stars.forEach(s => {
            s.alpha += s.dAlpha;
            if (s.alpha >= 1)   { s.alpha = 1;   s.dAlpha *= -1; }
            if (s.alpha <= 0.2) { s.alpha = 0.2; s.dAlpha *= -1; }

            ctx.beginPath();
            ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(${s.color},${s.alpha})`;
            ctx.fill();

            // Bigger stars get a soft glow
            if (s.r > 1.2) {
                const glow = ctx.createRadialGradient(s.x, s.y, 0, s.x, s.y, s.r * 4);
                glow.addColorStop(0, `rgba(${s.color},${s.alpha * 0.4})`);
                glow.addColorStop(1, `rgba(${s.color},0)`);
                ctx.fillStyle = glow;
                ctx.beginPath();
                ctx.arc(s.x, s.y, s.r * 4, 0, Math.PI * 2);
                ctx.fill();
            }
        });

        // Shooting stars
        shooters.forEach((s, i) => {
            if (!s.active) return;

            const tailX = s.x - Math.cos(Math.atan2(s.vy, s.vx)) * s.len;
            const tailY = s.y - Math.sin(Math.atan2(s.vy, s.vx)) * s.len;

            const grad = ctx.createLinearGradient(tailX, tailY, s.x, s.y);
            grad.addColorStop(0, `rgba(255,255,255,0)`);
            grad.addColorStop(1, `rgba(255,255,255,${s.alpha})`);

            ctx.beginPath();
            ctx.moveTo(tailX, tailY);
            ctx.lineTo(s.x, s.y);
            ctx.strokeStyle = grad;
            ctx.lineWidth   = 1.5;
            ctx.stroke();

            // Head glow
            const headGlow = ctx.createRadialGradient(s.x, s.y, 0, s.x, s.y, 6);
            headGlow.addColorStop(0, `rgba(255,255,255,${s.alpha})`);
            headGlow.addColorStop(1, `rgba(255,255,255,0)`);
            ctx.fillStyle = headGlow;
            ctx.beginPath();
            ctx.arc(s.x, s.y, 6, 0, Math.PI * 2);
            ctx.fill();

            s.x += s.vx;
            s.y += s.vy;
            s.alpha -= 0.012;

            if (s.x > W + 200 || s.y > H + 200 || s.alpha <= 0) {
                shooters[i] = newShooter();
                shooters[i].x = rand(-50, W * 0.6);
                shooters[i].y = rand(0, H * 0.4);
            }
        });

        requestAnimationFrame(draw);
    }

    initStars();
    initShooters();
    draw();
})();
function showToast(msg) {
    let t = document.getElementById('toast-msg');
    if (!t) {
        t = document.createElement('div');
        t.id = 'toast-msg';
        t.className = 'toast-msg';
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
}

document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    const msg = params.get('msg');
    if (msg === 'added')   showToast('✅ Movie added successfully!');
    if (msg === 'updated') showToast('✏️ Movie updated!');
    if (msg === 'deleted') showToast('🗑️ Movie deleted.');

    if (msg) {
        params.delete('msg');
        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        history.replaceState({}, '', newUrl);
    }
});

function confirmDelete(movieId, movieTitle) {
    const existing = document.getElementById('delete-modal-bg');
    if (existing) existing.remove();

    const bg = document.createElement('div');
    bg.id = 'delete-modal-bg';
    bg.className = 'delete-modal-bg';
    bg.innerHTML = `
        <div class="delete-modal">
            <h3>Delete Movie</h3>
            <p>Are you sure you want to delete <strong>${escHtml(movieTitle)}</strong>? This cannot be undone.</p>
            <div class="delete-modal-actions">
                <button class="btn-secondary" onclick="document.getElementById('delete-modal-bg').remove()">Cancel</button>
                <a href="delete_movie.php?id=${movieId}" class="btn-danger">Yes, Delete</a>
            </div>
        </div>
    `;
    bg.addEventListener('click', function(e) {
        if (e.target === bg) bg.remove();
    });
    document.body.appendChild(bg);
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

document.addEventListener('DOMContentLoaded', function () {
    const posterInput = document.getElementById('poster_url');
    if (!posterInput) return;

    let preview = document.getElementById('poster-preview');
    if (!preview) {
        preview = document.createElement('img');
        preview.id = 'poster-preview';
        preview.style.cssText = 'width:80px;height:120px;object-fit:cover;border-radius:6px;margin-top:8px;display:none;border:0.5px solid rgba(0,0,0,.1)';
        posterInput.parentNode.appendChild(preview);
    }

    function updatePreview() {
        const val = posterInput.value.trim();
        if (val) {
            preview.src = val;
            preview.style.display = 'block';
            preview.onerror = () => { preview.style.display = 'none'; };
        } else {
            preview.style.display = 'none';
        }
    }

    posterInput.addEventListener('input', updatePreview);
    updatePreview();
});