<!doctype html>
<html lang="uz">
<head>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="csrf-token" content="CSRF_TOKEN_HERE">
  <title>DonoxonSI — Uzun tumani yordamchi chat</title>
  <link rel="icon" type="image/png" href="logo.png">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    
    :root {
      --bg-primary: #0f172a;
      --bg-secondary: #1e293b;
      --bg-card: #1e293b;
      --bg-input: #334155;
      --text-primary: #f1f5f9;
      --text-secondary: #cbd5e1;
      --text-muted: #94a3b8;
      --accent-primary: #d97706;
      --accent-secondary: #f59e0b;
      --user-bubble: linear-gradient(135deg, #f59e0b, #d97706);
      --ai-bubble: #334155;
      --border: rgba(255,255,255,0.1);
      --shadow: rgba(0,0,0,0.5);
    }
    [data-theme="light"] {
      --bg-primary: #f8fafc;
      --bg-secondary: #ffffff;
      --bg-card: #ffffff;
      --bg-input: #f1f5f9;
      --text-primary: #0f172a;
      --text-secondary: #334155;
      --text-muted: #64748b;
      --accent-primary: #d97706;
      --accent-secondary: #f59e0b;
      --user-bubble: linear-gradient(135deg, #f59e0b, #d97706);
      --ai-bubble: #f1f5f9;
      --border: rgba(0,0,0,0.1);
      --shadow: rgba(0,0,0,0.1);
    }
    html,body{height:100%}
    body{
      background: var(--bg-primary);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, Arial, sans-serif;
      color: var(--text-primary);
      transition: background 0.3s ease, color 0.3s ease;
    }
    .container{
      height:100vh;
      display:flex;
      flex-direction:column;
      max-width:1400px;
      margin:0 auto;
    }
    .header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:16px 24px;
      background: var(--bg-secondary);
      border-bottom:1px solid var(--border);
      box-shadow:0 2px 8px var(--shadow);
    }
    .header-left{
      display:flex;
      align-items:center;
      gap:14px;
    }
    .header-right{
      display:flex;
      align-items:center;
      gap:12px;
    }
    .logo{
      width:48px;
      height:48px;
      border-radius:12px;
      background: linear-gradient(135deg, #f59e0b, #d97706);
      display:flex;
      align-items:center;
      justify-content:center;
      font-weight:700;
      font-size:22px;
      color:white;
      box-shadow:0 4px 12px rgba(217,119,6,0.3);
      overflow:hidden;
    }
    .logo img{
      width:100%;
      height:100%;
      object-fit:cover;
    }
    .logo img[style*="display:none"] + .logo-text{
      display:block !important;
    }
    .logo-text{
      font-weight:700;
      font-size:22px;
      color:white;
    }
    /* Loader Styles */
    .page-loader{
      position:fixed;
      inset:0;
      background: linear-gradient(135deg, #f59e0b, #d97706);
      display:flex;
      flex-direction:column;
      align-items:center;
      justify-content:center;
      z-index:9999;
      transition:opacity 0.5s ease, visibility 0.5s ease;
    }
    .page-loader.hidden{
      opacity:0;
      visibility:hidden;
    }
    .loader-logo{
      width:80px;
      height:80px;
      border-radius:20px;
      background:white;
      display:flex;
      align-items:center;
      justify-content:center;
      margin-bottom:30px;
      animation:pulse 2s ease-in-out infinite;
      box-shadow:0 10px 40px rgba(0,0,0,0.3);
    }
    .loader-logo img{
      width:60px;
      height:60px;
      object-fit:contain;
    }
    @keyframes pulse{
      0%, 100%{transform:scale(1)}
      50%{transform:scale(1.05)}
    }
    .loader {
      animation: rotate 1s infinite;
      height: 50px;
      width: 50px;
    }
    .loader:before,
    .loader:after {
      border-radius: 50%;
      content: "";
      display: block;
      height: 20px;
      width: 20px;
    }
    .loader:before {
      animation: ball1 1s infinite;
      background-color: #fff;
      box-shadow: 30px 0 0 #ff3d00;
      margin-bottom: 10px;
    }
    .loader:after {
      animation: ball2 1s infinite;
      background-color: #ff3d00;
      box-shadow: 30px 0 0 #fff;
    }
    @keyframes rotate {
      0% { transform: rotate(0deg) scale(0.8) }
      50% { transform: rotate(360deg) scale(1.2) }
      100% { transform: rotate(720deg) scale(0.8) }
    }
    @keyframes ball1 {
      0% {
        box-shadow: 30px 0 0 #ff3d00;
      }
      50% {
        box-shadow: 0 0 0 #ff3d00;
        margin-bottom: 0;
        transform: translate(15px, 15px);
      }
      100% {
        box-shadow: 30px 0 0 #ff3d00;
        margin-bottom: 10px;
      }
    }
    @keyframes ball2 {
      0% {
        box-shadow: 30px 0 0 #fff;
      }
      50% {
        box-shadow: 0 0 0 #fff;
        margin-top: -20px;
        transform: translate(15px, 15px);
      }
      100% {
        box-shadow: 30px 0 0 #fff;
        margin-top: 0;
      }
    }
    .loader-text{
      margin-top:30px;
      color:white;
      font-size:16px;
      font-weight:500;
    }
    .header-info h1{
      font-size:20px;
      font-weight:700;
      color: var(--text-primary);
      margin-bottom:2px;
    }
    .header-info p{
      font-size:13px;
      color: var(--text-muted);
    }
    
    /* Request Button */
    .request-btn, .theme-toggle{
      width:48px;
      height:48px;
      border-radius:12px;
      background: var(--bg-input);
      border:1px solid var(--border);
      cursor:pointer;
      display:flex;
      align-items:center;
      justify-content:center;
      transition:all 0.3s ease;
      position:relative;
    }
    .request-btn{
      background: linear-gradient(135deg, #f59e0b, #d97706);
      border:none;
      box-shadow:0 4px 12px rgba(217,119,6,0.3);
    }
    .request-btn:hover, .theme-toggle:hover{
      transform:scale(1.05);
      box-shadow:0 4px 12px var(--shadow);
    }
    .request-btn svg, .theme-toggle svg{
      width:22px;
      height:22px;
      transition:transform 0.3s ease;
    }
    .request-btn svg{
      fill:white;
    }
    .theme-toggle svg{
      fill: var(--text-primary);
    }
    .request-btn:hover svg{
      transform:scale(1.1);
    }
    .theme-toggle:hover svg{
      transform:rotate(20deg);
    }
    
    /* Modal Styles */
    .modal-overlay{
      position:fixed;
      inset:0;
      background:rgba(0,0,0,0.7);
      display:none;
      align-items:center;
      justify-content:center;
      z-index:1000;
      padding:20px;
      animation:fadeIn 0.3s ease;
    }
    .modal-overlay.active{
      display:flex;
    }
    @keyframes fadeIn{
      from{opacity:0}
      to{opacity:1}
    }
    .modal{
      background: var(--bg-card);
      border-radius:20px;
      padding:32px;
      max-width:500px;
      width:100%;
      box-shadow:0 20px 60px rgba(0,0,0,0.5);
      border:1px solid var(--border);
      animation:slideUp 0.3s ease;
    }
    @keyframes slideUp{
      from{transform:translateY(30px);opacity:0}
      to{transform:translateY(0);opacity:1}
    }
    .modal-header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      margin-bottom:24px;
    }
    .modal-title{
      font-size:24px;
      font-weight:700;
      color: var(--text-primary);
      display:flex;
      align-items:center;
      gap:12px;
    }
    .modal-title svg{
      width:28px;
      height:28px;
      fill: #f59e0b;
    }
    .modal-close{
      width:36px;
      height:36px;
      border-radius:8px;
      background: var(--bg-input);
      border:none;
      cursor:pointer;
      display:flex;
      align-items:center;
      justify-content:center;
      transition:all 0.2s ease;
    }
    .modal-close:hover{
      background: #ef4444;
      transform:scale(1.05);
    }
    .modal-close svg{
      width:18px;
      height:18px;
      stroke: var(--text-primary);
    }
    .modal-close:hover svg{
      stroke:white;
    }
    .form-group{
      margin-bottom:20px;
    }
    .form-label{
      display:block;
      font-size:14px;
      font-weight:600;
      color: var(--text-secondary);
      margin-bottom:8px;
    }
    .form-input, .form-textarea{
      width:100%;
      background: var(--bg-input);
      border-radius:12px;
      padding:14px 16px;
      border:2px solid transparent;
      color: var(--text-primary);
      font-size:15px;
      outline:none;
      transition:all 0.3s ease;
      font-family:inherit;
    }
    .form-input::placeholder, .form-textarea::placeholder{
      color: var(--text-muted);
    }
    .form-input:focus, .form-textarea:focus{
      border-color: #f59e0b;
      background: var(--bg-card);
      box-shadow:0 0 0 3px rgba(245,158,11,0.1);
    }
    .form-textarea{
      resize:vertical;
      min-height:120px;
    }
    .form-submit{
      width:100%;
      background: linear-gradient(135deg, #f59e0b, #d97706);
      border:none;
      border-radius:12px;
      padding:14px;
      color:white;
      font-size:16px;
      font-weight:600;
      cursor:pointer;
      transition:all 0.3s ease;
      box-shadow:0 4px 16px rgba(217,119,6,0.3);
      display:flex;
      align-items:center;
      justify-content:center;
      gap:8px;
    }
    .form-submit:hover{
      transform:translateY(-2px);
      box-shadow:0 6px 20px rgba(217,119,6,0.4);
    }
    .form-submit:active{
      transform:translateY(0);
    }
    .form-submit:disabled{
      opacity:0.6;
      cursor:not-allowed;
      transform:none;
    }
    
    /* Alert Messages */
    .alert{
      padding:14px 18px;
      border-radius:12px;
      margin-bottom:20px;
      display:flex;
      align-items:center;
      gap:12px;
      animation:slideIn 0.3s ease;
    }
    .alert-success{
      background: rgba(34,197,94,0.15);
      border:1px solid rgba(34,197,94,0.3);
      color: #22c55e;
    }
    .alert-error{
      background: rgba(239,68,68,0.15);
      border:1px solid rgba(239,68,68,0.3);
      color: #ef4444;
    }
    .alert svg{
      width:20px;
      height:20px;
      flex-shrink:0;
    }
    
    .chat-container{
      flex:1;
      display:flex;
      flex-direction:column;
      overflow:hidden;
      background: var(--bg-primary);
    }
    .messages{
      flex:1;
      padding:24px;
      overflow-y:auto;
      display:flex;
      flex-direction:column;
      gap:16px;
      scroll-behavior: smooth;
    }
    .messages::-webkit-scrollbar{width:8px}
    .messages::-webkit-scrollbar-track{background:transparent}
    .messages::-webkit-scrollbar-thumb{
      background: var(--bg-input);
      border-radius:4px;
    }
    .msg{
      max-width:75%;
      display:flex;
      flex-direction:column;
      gap:8px;
      animation:slideIn 0.3s ease;
    }
    @keyframes slideIn{
      from{opacity:0;transform:translateY(10px)}
      to{opacity:1;transform:translateY(0)}
    }
    .bubble{
      padding:14px 18px;
      border-radius:16px;
      line-height:1.6;
      font-size:15px;
      white-space:pre-wrap;
      word-wrap:break-word;
      box-shadow:0 2px 8px var(--shadow);
      transition:transform 0.2s ease;
    }
    .bubble:hover{
      transform:translateY(-2px);
    }
    .user{align-self:flex-end}
    .user .bubble{
      background: var(--user-bubble);
      color:white;
      border-bottom-right-radius:4px;
    }
    .ai{align-self:flex-start}
    .ai .bubble{
      background: var(--ai-bubble);
      color: var(--text-primary);
      border-bottom-left-radius:4px;
      border:1px solid var(--border);
    }
    .typing{align-self:flex-start}
    .typing .bubble{
      background: var(--ai-bubble);
      border:1px solid var(--border);
      display:flex;
      align-items:center;
      gap:8px;
    }
    .typing .dot{
      width:8px;
      height:8px;
      border-radius:50%;
      background: #f59e0b;
      display:inline-block;
      animation:typing 1.2s infinite;
    }
    .typing .dot:nth-child(2){animation-delay:.2s}
    .typing .dot:nth-child(3){animation-delay:.4s}
    @keyframes typing{
      0%, 100%{opacity:.3;transform:translateY(0)}
      50%{opacity:1;transform:translateY(-6px)}
    }
    .composer{
      display:flex;
      align-items:center;
      gap:12px;
      padding:20px 24px;
      background: var(--bg-secondary);
      border-top:1px solid var(--border);
      box-shadow:0 -2px 8px var(--shadow);
    }
    .input-wrapper{
      flex:1;
      position:relative;
    }
    .input{
      width:100%;
      background: var(--bg-input);
      border-radius:14px;
      padding:14px 18px;
      border:2px solid transparent;
      color: var(--text-primary);
      font-size:15px;
      outline:none;
      transition:all 0.3s ease;
      font-family:inherit;
    }
    .input::placeholder{
      color: var(--text-muted);
    }
    .input:focus{
      border-color: #f59e0b;
      background: var(--bg-card);
      box-shadow:0 0 0 3px rgba(245,158,11,0.1);
    }
    .send-btn{
      min-width:54px;
      height:54px;
      border-radius:14px;
      background: linear-gradient(135deg, #f59e0b, #d97706);
      border:none;
      cursor:pointer;
      display:flex;
      align-items:center;
      justify-content:center;
      box-shadow:0 4px 16px rgba(217,119,6,0.3);
      transition:all 0.3s ease;
    }
    .send-btn:hover{
      transform:translateY(-2px) scale(1.05);
      box-shadow:0 6px 20px rgba(217,119,6,0.4);
    }
    .send-btn:active{
      transform:translateY(0) scale(0.98);
    }
    .send-btn svg{
      width:20px;
      height:20px;
    }
    @media(max-width:768px){
      .header{padding:12px 16px}
      
      .logo{width:42px;height:42px;font-size:20px}
      .logo-text{font-size:20px}
      
      .header-info h1{font-size:18px}
      .header-info p{font-size:12px}
      
      .request-btn, .theme-toggle{width:42px;height:42px}
      .request-btn svg, .theme-toggle svg{width:20px;height:20px}
      
      .messages{padding:16px;gap:12px}
      
      .msg{max-width:85%}
      
      .bubble{padding:12px 16px;font-size:14px}
      
      .composer{padding:16px;gap:10px}
      
      .input{padding:12px 16px;font-size:14px}
      
      .send-btn{min-width:48px;height:48px}
      .send-btn svg{width:18px;height:18px}
      
      .modal{padding:24px}
      .modal-title{font-size:20px}
    }
    @media(max-width:480px){
      .header-left{gap:10px}
      .logo{width:38px;height:38px;font-size:18px}
      .logo-text{font-size:18px}
      .header-info h1{font-size:16px}
      .request-btn, .theme-toggle{width:38px;height:38px}
      .msg{max-width:90%}
      .bubble{padding:10px 14px;font-size:14px}
      .modal{padding:20px}
    }
  </style>
</head>
<body>
<!-- Page Loader -->
<div class="page-loader" id="pageLoader">
  <div class="loader-logo">
    <img src="logo.png" alt="DonoxonSI">
  </div>
  <span class="loader"></span>
  <div class="loader-text">Yuklanmoqda...</div>
</div>

<!-- Request Modal -->
<div class="modal-overlay" id="requestModal">
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
        </svg>
        Murojaat qoldirish
      </h2>
      <button class="modal-close" id="closeModal">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/>
          <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
    
    <div id="alertContainer"></div>
    
    <form id="requestForm">
      <div class="form-group">
        <label class="form-label">Ism-Familiya</label>
        <input type="text" class="form-input" id="fullName" placeholder="Masalan: Alisher Navoiy" required>
      </div>
      
      <div class="form-group">
        <label class="form-label">Xabaringiz</label>
        <textarea class="form-textarea" id="requestText" placeholder="Murojaat yoki so'rovingizni yozing..." required></textarea>
      </div>
      
      <button type="submit" class="form-submit" id="submitBtn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="22" y1="2" x2="11" y2="13"/>
          <polygon points="22 2 15 22 11 13 2 9 22 2"/>
        </svg>
        Yuborish
      </button>
    </form>
  </div>
</div>

<div class="container">
  <div class="header">
    <div class="header-left">
      <div class="logo">
        <img src="logo.png" alt="DonoxonSI Logo">
        <span class="logo-text" style="display:none">D</span>
      </div>
      <div class="header-info">
        <h1>DonoxonSI</h1>
        <p>Sun'iy intellekt yordamchi</p>
      </div>
    </div>
    
    <div class="header-right">
      <button class="request-btn" id="openRequestBtn" aria-label="Murojaat qoldirish">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
        </svg>
      </button>
      
      <button class="theme-toggle" id="themeToggle" aria-label="Rejimni o'zgartirish">
        <svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="4"/>
          <path d="M12 2v2"/>
          <path d="M12 20v2"/>
          <path d="m4.93 4.93 1.41 1.41"/>
          <path d="m17.66 17.66 1.41 1.41"/>
          <path d="M2 12h2"/>
          <path d="M20 12h2"/>
          <path d="m6.34 17.66-1.41 1.41"/>
          <path d="m19.07 4.93-1.41 1.41"/>
        </svg>
        <svg class="moon-icon" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
        </svg>
      </button>
    </div>
  </div>
  
  <div class="chat-container">
    <div id="messages" class="messages"></div>
    <div class="composer">
      <div class="input-wrapper">
        <input id="message_input" class="input" type="text" placeholder="Xabar yozing..." autocomplete="off" />
      </div>
      <button id="sendBtn" class="send-btn" aria-label="Yuborish">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
          <line x1="22" y1="2" x2="11" y2="13"/>
          <polygon points="22 2 15 22 11 13 2 9 22 2"/>
        </svg>
      </button>
    </div>
  </div>
</div>

<script>
  // Page Loader
  window.addEventListener('load', function() {
    const loader = document.getElementById('pageLoader');
    setTimeout(() => {
      loader.classList.add('hidden');
    }, 1500);
  });
  
  // Logo error handling
  const logoImg = document.querySelector('.logo img');
  if(logoImg) {
    logoImg.onerror = function() {
      this.style.display = 'none';
      this.nextElementSibling.style.display = 'block';
    };
  }
  
  // Chat elements
  const messagesEl = document.getElementById('messages');
  const input = document.getElementById('message_input');
  const sendBtn = document.getElementById('sendBtn');
  const themeToggle = document.getElementById('themeToggle');
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const chatRoute = '/chat/message';
  
  // Request modal elements
  const requestModal = document.getElementById('requestModal');
  const openRequestBtn = document.getElementById('openRequestBtn');
  const closeModalBtn = document.getElementById('closeModal');
  const requestForm = document.getElementById('requestForm');
  const fullNameInput = document.getElementById('fullName');
  const requestTextInput = document.getElementById('requestText');
  const alertContainer = document.getElementById('alertContainer');
  const submitBtn = document.getElementById('submitBtn');
  const requestRoute = '/chat/submit-request'; // Laravel route
  
  // Theme management
  const savedTheme = localStorage.getItem('theme') || 'dark';
  document.documentElement.setAttribute('data-theme', savedTheme);
  updateThemeIcon(savedTheme);
  
  themeToggle.addEventListener('click', () => {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcon(newTheme);
  });
  
  function updateThemeIcon(theme) {
    const sunIcon = themeToggle.querySelector('.sun-icon');
    const moonIcon = themeToggle.querySelector('.moon-icon');
    if (theme === 'dark') {
      sunIcon.style.display = 'block';
      moonIcon.style.display = 'none';
    } else {
      sunIcon.style.display = 'none';
      moonIcon.style.display = 'block';
    }
  }
  
  // Modal management
  openRequestBtn.addEventListener('click', () => {
    requestModal.classList.add('active');
    fullNameInput.focus();
  });
  
  closeModalBtn.addEventListener('click', () => {
    requestModal.classList.remove('active');
    clearForm();
  });
  
  requestModal.addEventListener('click', (e) => {
    if (e.target === requestModal) {
      requestModal.classList.remove('active');
      clearForm();
    }
  });
  
  // Alert functions
  function showAlert(message, type) {
    const iconSvg = type === 'success' 
      ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>'
      : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
    
    alertContainer.innerHTML = `
      <div class="alert alert-${type}">
        ${iconSvg}
        <span>${message}</span>
      </div>
    `;
    
    if (type === 'success') {
      setTimeout(() => {
        requestModal.classList.remove('active');
        clearForm();
      }, 2000);
    }
  }
  
  function clearAlert() {
    alertContainer.innerHTML = '';
  }
  
  function clearForm() {
    requestForm.reset();
    clearAlert();
  }
  
  // Request form submission
  requestForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearAlert();
    
    const fullName = fullNameInput.value.trim();
    const requestText = requestTextInput.value.trim();
    
    if (!fullName || !requestText) {
      showAlert('Iltimos, barcha maydonlarni to\'ldiring', 'error');
      return;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
      <svg style="animation: rotate 1s linear infinite" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
      </svg>
      Yuborilmoqda...
    `;
    
    try {
      const response = await fetch(requestRoute, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify({
          full_name: fullName,
          request: requestText
        })
      });
      
      const data = await response.json();
      
      if (response.ok && data.status === 'success') {
        showAlert(data.message, 'success');
      } else {
        showAlert(data.message || 'Xatolik yuz berdi', 'error');
      }
    } catch (error) {
      showAlert('Tarmoq xatosi. Iltimos, qaytadan urinib ko\'ring', 'error');
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="22" y1="2" x2="11" y2="13"/>
          <polygon points="22 2 15 22 11 13 2 9 22 2"/>
        </svg>
        Yuborish
      `;
    }
  });
  
  // Chat functions
  function esc(s){
    return String(s).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;');
  }
  
  function appendUser(text){
    const w = document.createElement('div');
    w.className = "msg user";
    w.innerHTML = `<div class="bubble">${esc(text)}</div>`;
    messagesEl.appendChild(w);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }
  
  function appendTyping(){
    const w = document.createElement('div');
    w.className = "msg typing";
    w.innerHTML = `
      <div class="bubble">
        <span class="dot"></span>
        <span class="dot"></span>
        <span class="dot"></span>
      </div>`;
    messagesEl.appendChild(w);
    messagesEl.scrollTop = messagesEl.scrollHeight;
    return w;
  }
  
  function showAI(w, text){
    w.classList.remove("typing");
    w.classList.add("ai");
    w.innerHTML = `<div class="bubble">${esc(text)}</div>`;
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }
  
  async function send(){
    const text = input.value.trim();
    if(!text) return;
    appendUser(text);
    input.value = "";
    const typingEl = appendTyping();
    try {
      const res = await fetch(chatRoute, {
        method:"POST",
        headers:{
          "Content-Type":"application/json",
          "X-CSRF-TOKEN":csrf
        },
        body:JSON.stringify({ message:text })
      });
      const data = await res.json();
      setTimeout(()=>{
        showAI(typingEl, data?.reply ?? "Xatolik yuz berdi");
      }, 600 + Math.random()*600);
    } catch(err){
      showAI(typingEl, "Tarmoq xatosi");
    }
  }
  
  sendBtn.onclick = send;
  input.addEventListener("keydown", e => e.key === "Enter" && send());
</script>
</body>
</html>