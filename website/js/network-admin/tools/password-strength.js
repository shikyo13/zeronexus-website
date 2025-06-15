/**
 * Password Strength Tester & Generator
 * Provides password strength analysis and secure password generation
 */

// Character sets for password generation
const CHAR_SETS = {
  uppercase: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
  lowercase: 'abcdefghijklmnopqrstuvwxyz',
  numbers: '0123456789',
  symbols: '!@#$%^&*()_+-=[]{}|;:,.<>?'
};

// Common password patterns to detect
const COMMON_PATTERNS = [
  /^(password|123456|qwerty|admin|letmein|welcome|monkey|dragon)/i,
  /^(\d)\1+$/,  // Repeated digits
  /^([a-z])\1+$/i,  // Repeated letters
  /^(abc|xyz|123|qwe)/i,  // Sequential patterns
  /^(.)\1{2,}$/  // Same character repeated 3+ times
];

// Advanced patterns for enhanced detection
const ADVANCED_PATTERNS = {
  keyboardWalks: [
    /qwerty|asdf|zxcv|yuiop|hjkl|bnm/i,
    /123456|234567|345678|456789|567890/,
    /qwertyui|asdfghjk|zxcvbnm/i,
    /1qaz2wsx|qazwsx|zaq1xsw2/i
  ],
  leetSpeak: [
    /p@ssw0rd|p4ssw0rd|passw0rd/i,
    /[a@]dm[i1]n|4dm1n/i,
    /[3e]l[i1]t[3e]|3l1t3/i,
    /h4ck3r|h@ck3r/i,
    /[0o]n[3e]|tw[0o]/i
  ],
  dates: [
    /19\d{2}|20\d{2}/,  // Years
    /\d{2}\/\d{2}\/\d{4}/,  // Dates
    /\d{4}-\d{2}-\d{2}/,  // ISO dates
    /(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\d{2,4}/i
  ],
  sequences: [
    /abcd|bcde|cdef|defg/i,
    /1234|2345|3456|4567|5678|6789|7890/,
    /zyxw|yxwv|xwvu|wvut/i
  ]
};

// Common dictionary words (subset for demo)
const COMMON_WORDS = [
  'password', 'admin', 'user', 'login', 'welcome', 'guest', 'test', 'demo',
  'master', 'root', 'super', 'secret', 'private', 'system', 'account',
  'database', 'server', 'network', 'computer', 'internet', 'email', 'web'
];

// Strength levels
const STRENGTH_LEVELS = {
  0: { label: 'Very Weak', class: 'bg-danger', color: '#dc3545' },
  1: { label: 'Weak', class: 'bg-warning', color: '#ffc107' },
  2: { label: 'Fair', class: 'bg-info', color: '#0dcaf0' },
  3: { label: 'Good', class: 'bg-primary', color: '#0d6efd' },
  4: { label: 'Strong', class: 'bg-success', color: '#198754' }
};

// Session storage for password history
let passwordHistory = [];

/**
 * Initialize Password Strength Tool
 */
export default function setupPasswordStrength() {
  console.log('Setting up Password Strength Tool...');
  
  // Set up event listeners
  setupPasswordTester();
  setupPasswordGenerator();
  
  // Initialize UI elements
  initializeUI();
  
  // Debug checkbox elements
  setTimeout(() => {
    console.log('=== Password Tool Debug ===');
    const checkboxes = ['includeUppercase', 'includeLowercase', 'includeNumbers', 'includeSymbols', 'advancedAnalysis'];
    checkboxes.forEach(id => {
      const element = document.getElementById(id);
      console.log(`${id}:`, element ? 'found' : 'NOT FOUND', element ? `checked: ${element.checked}` : '');
    });
  }, 1000);
}

/**
 * Initialize UI elements
 */
function initializeUI() {
  // No longer needed - slider was removed in favor of number input only
  console.log('Password tool UI initialized');
}

/**
 * Set up password tester functionality
 */
function setupPasswordTester() {
  const passwordInput = document.getElementById('passwordInput');
  const toggleBtn = document.getElementById('togglePasswordVisibility');
  const advancedCheckbox = document.getElementById('advancedAnalysis');
  
  if (!passwordInput) return;
  
  // Real-time password analysis
  let debounceTimer;
  passwordInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      analyzePassword(passwordInput.value);
    }, 300);
  });
  
  // Toggle password visibility
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      const type = passwordInput.type === 'password' ? 'text' : 'password';
      passwordInput.type = type;
      toggleBtn.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
    });
  }
  
  // Advanced analysis toggle
  if (advancedCheckbox) {
    advancedCheckbox.addEventListener('change', () => {
      console.log('Advanced analysis toggled:', advancedCheckbox.checked);
      if (passwordInput && passwordInput.value) {
        analyzePassword(passwordInput.value);
      }
      
      // Show feedback that advanced analysis is enabled/disabled
      const feedbackEl = document.getElementById('passwordFeedback');
      if (feedbackEl && advancedCheckbox.checked) {
        const existingAdvancedNote = feedbackEl.querySelector('.advanced-analysis-note');
        if (!existingAdvancedNote) {
          const note = document.createElement('div');
          note.className = 'alert alert-info mt-2 advanced-analysis-note';
          note.innerHTML = '<i class="fas fa-brain me-2"></i>Advanced pattern detection enabled - More thorough analysis active';
          feedbackEl.appendChild(note);
        }
      } else if (feedbackEl) {
        const existingAdvancedNote = feedbackEl.querySelector('.advanced-analysis-note');
        if (existingAdvancedNote) {
          existingAdvancedNote.remove();
        }
      }
    });
  } else {
    console.warn('Advanced analysis checkbox not found');
  }
}

/**
 * Analyze password strength
 */
async function analyzePassword(password) {
  if (!password) {
    hidePasswordAnalysis();
    return;
  }
  
  // Calculate basic entropy
  const entropy = calculateEntropy(password);
  const score = calculateStrengthScore(password, entropy);
  
  // Update UI
  updateStrengthMeter(score);
  await updateAnalysisDetails(password, entropy, score);
  
  // Show analysis sections
  document.getElementById('passwordStrengthMeter')?.classList.remove('d-none');
  document.getElementById('passwordAnalysis')?.classList.remove('d-none');
}

/**
 * Calculate password entropy
 */
function calculateEntropy(password) {
  const charsetSize = getCharsetSize(password);
  return Math.log2(Math.pow(charsetSize, password.length));
}

/**
 * Get charset size based on password content
 */
function getCharsetSize(password) {
  let size = 0;
  
  if (/[a-z]/.test(password)) size += 26;
  if (/[A-Z]/.test(password)) size += 26;
  if (/[0-9]/.test(password)) size += 10;
  if (/[^a-zA-Z0-9]/.test(password)) size += 32;
  
  return size;
}

/**
 * Calculate overall strength score (0-4)
 */
function calculateStrengthScore(password, entropy) {
  let score = 0;
  let advancedPenalties = 0;
  
  // Length scoring
  if (password.length >= 8) score++;
  if (password.length >= 12) score++;
  if (password.length >= 16) score++;
  
  // Entropy scoring
  if (entropy >= 30) score++;
  if (entropy >= 50) score++;
  if (entropy >= 70) score++;
  
  // Character diversity
  const hasLower = /[a-z]/.test(password);
  const hasUpper = /[A-Z]/.test(password);
  const hasNumber = /[0-9]/.test(password);
  const hasSymbol = /[^a-zA-Z0-9]/.test(password);
  
  const diversity = [hasLower, hasUpper, hasNumber, hasSymbol].filter(Boolean).length;
  if (diversity >= 3) score++;
  if (diversity === 4) score++;
  
  // Penalties
  if (password.length < 8) score = 0;
  
  // Check common patterns
  for (const pattern of COMMON_PATTERNS) {
    if (pattern.test(password)) {
      score = Math.max(0, score - 2);
      break;
    }
  }
  
  // Advanced analysis penalties (only if enabled)
  const advancedEnabled = document.getElementById('advancedAnalysis')?.checked;
  if (advancedEnabled) {
    const advancedIssues = performAdvancedAnalysis(password);
    advancedPenalties = advancedIssues.length;
    score = Math.max(0, score - advancedPenalties);
  }
  
  // Normalize score to 0-4
  return Math.min(4, Math.max(0, Math.floor(score / 2)));
}

/**
 * Perform advanced password analysis
 */
function performAdvancedAnalysis(password) {
  const issues = [];
  
  // Check keyboard walks
  for (const pattern of ADVANCED_PATTERNS.keyboardWalks) {
    if (pattern.test(password)) {
      issues.push('Contains keyboard patterns (e.g., qwerty, asdf)');
      break;
    }
  }
  
  // Check leetspeak
  for (const pattern of ADVANCED_PATTERNS.leetSpeak) {
    if (pattern.test(password)) {
      issues.push('Uses common leetspeak substitutions');
      break;
    }
  }
  
  // Check dates
  for (const pattern of ADVANCED_PATTERNS.dates) {
    if (pattern.test(password)) {
      issues.push('Contains dates or years');
      break;
    }
  }
  
  // Check sequences
  for (const pattern of ADVANCED_PATTERNS.sequences) {
    if (pattern.test(password)) {
      issues.push('Contains sequential characters');
      break;
    }
  }
  
  // Check dictionary words
  const lowerPassword = password.toLowerCase();
  for (const word of COMMON_WORDS) {
    if (lowerPassword.includes(word)) {
      issues.push('Contains common dictionary words');
      break;
    }
  }
  
  return issues;
}

/**
 * Check password against HaveIBeenPwned database
 */
async function checkHaveIBeenPwned(password) {
  try {
    console.log('Checking password against HaveIBeenPwned...');
    
    // Use SHA-1 hash of password (first 5 chars for k-anonymity)
    const encoder = new TextEncoder();
    const data = encoder.encode(password);
    const hashBuffer = await crypto.subtle.digest('SHA-1', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('').toUpperCase();
    
    const prefix = hashHex.substring(0, 5);
    const suffix = hashHex.substring(5);
    
    console.log(`Hash prefix: ${prefix}, looking for suffix: ${suffix}`);
    
    // Use PHP proxy to avoid CORS issues
    const response = await fetch('/api/hibp-proxy.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        prefix: prefix
      })
    });
    
    if (!response.ok) {
      throw new Error(`Proxy request failed with status: ${response.status}`);
    }
    
    const result = await response.json();
    
    if (!result.success) {
      throw new Error(result.error || 'Unknown proxy error');
    }
    
    const text = result.data;
    console.log(`Received ${text.split('\n').length} hash lines from API`);
    
    const lines = text.split('\n');
    
    for (const line of lines) {
      const [hashSuffix, count] = line.split(':');
      if (hashSuffix && hashSuffix.trim() === suffix) {
        const breachCount = parseInt(count, 10);
        console.log(`Password found in breaches: ${breachCount}`);
        return breachCount;
      }
    }
    
    console.log('Password not found in breaches');
    return 0; // Not found in breaches
  } catch (error) {
    console.error('HaveIBeenPwned check failed:', error);
    return null; // Error occurred
  }
}

/**
 * Update strength meter UI
 */
function updateStrengthMeter(score) {
  const strengthBar = document.getElementById('strengthBar');
  const strengthLabel = document.getElementById('strengthLabel');
  
  if (!strengthBar || !strengthLabel) return;
  
  const level = STRENGTH_LEVELS[score];
  const percentage = (score + 1) * 20;
  
  strengthBar.style.width = percentage + '%';
  strengthBar.className = 'progress-bar ' + level.class;
  strengthBar.setAttribute('aria-valuenow', percentage);
  
  strengthLabel.textContent = level.label;
  strengthLabel.className = 'badge ' + level.class;
}

/**
 * Update analysis details
 */
async function updateAnalysisDetails(password, entropy, score) {
  const statsEl = document.getElementById('passwordStats');
  const feedbackEl = document.getElementById('passwordFeedback');
  
  if (!statsEl || !feedbackEl) return;
  
  // Basic stats
  let stats = [
    `<li><strong>Length:</strong> ${password.length} characters</li>`,
    `<li><strong>Entropy:</strong> ${entropy.toFixed(1)} bits</li>`,
    `<li><strong>Character Set Size:</strong> ${getCharsetSize(password)}</li>`,
    `<li><strong>Time to Crack:</strong> ${estimateCrackTime(entropy)}</li>`
  ];
  
  // Advanced analysis if enabled
  const advancedEnabled = document.getElementById('advancedAnalysis')?.checked;
  if (advancedEnabled) {
    // Add placeholder for breach check while it loads
    stats.push(`<li><strong class="text-info">Breach Database:</strong> Checking... <i class="fas fa-spinner fa-spin"></i></li>`);
    
    // Update stats first, then check breaches
    statsEl.innerHTML = stats.join('');
    
    // Add HaveIBeenPwned check
    const breachCount = await checkHaveIBeenPwned(password);
    
    // Update the breach check result
    if (breachCount !== null) {
      if (breachCount > 0) {
        stats[stats.length - 1] = `<li><strong class="text-danger">Breach Database:</strong> Found in ${breachCount.toLocaleString()} breaches! ⚠️</li>`;
      } else {
        stats[stats.length - 1] = `<li><strong class="text-success">Breach Database:</strong> Not found in known breaches ✓</li>`;
      }
    } else {
      stats[stats.length - 1] = `<li><strong class="text-warning">Breach Database:</strong> Check failed (CORS/network error)</li>`;
    }
    
    // Update stats again with final result
    statsEl.innerHTML = stats.join('');
    return; // Early return since we already updated statsEl
  }
  
  statsEl.innerHTML = stats.join('');
  
  // Feedback with advanced analysis
  const feedback = await generateFeedback(password, score, advancedEnabled);
  feedbackEl.innerHTML = feedback;
}

/**
 * Estimate time to crack based on entropy
 */
function estimateCrackTime(entropy) {
  // Assuming 1 trillion guesses per second
  const guessesPerSecond = 1e12;
  const totalGuesses = Math.pow(2, entropy);
  const seconds = totalGuesses / (2 * guessesPerSecond); // Average case
  
  if (seconds < 1) return 'Instant';
  if (seconds < 60) return Math.round(seconds) + ' seconds';
  if (seconds < 3600) return Math.round(seconds / 60) + ' minutes';
  if (seconds < 86400) return Math.round(seconds / 3600) + ' hours';
  if (seconds < 2592000) return Math.round(seconds / 86400) + ' days';
  if (seconds < 31536000) return Math.round(seconds / 2592000) + ' months';
  
  const years = seconds / 31536000;
  if (years < 1000) return Math.round(years) + ' years';
  if (years < 1e6) return (years / 1000).toFixed(1) + ' thousand years';
  if (years < 1e9) return (years / 1e6).toFixed(1) + ' million years';
  return (years / 1e9).toFixed(1) + ' billion years';
}

/**
 * Generate feedback based on password analysis
 */
async function generateFeedback(password, score, advancedEnabled = false) {
  const feedback = [];
  
  if (score < 3) {
    feedback.push('<h6 class="text-warning">Suggestions:</h6>');
    feedback.push('<ul class="mb-0">');
    
    if (password.length < 12) {
      feedback.push('<li>Use at least 12 characters</li>');
    }
    
    if (!/[a-z]/.test(password) || !/[A-Z]/.test(password)) {
      feedback.push('<li>Mix uppercase and lowercase letters</li>');
    }
    
    if (!/[0-9]/.test(password)) {
      feedback.push('<li>Include numbers</li>');
    }
    
    if (!/[^a-zA-Z0-9]/.test(password)) {
      feedback.push('<li>Add special characters</li>');
    }
    
    // Basic pattern check
    for (const pattern of COMMON_PATTERNS) {
      if (pattern.test(password)) {
        feedback.push('<li class="text-danger">Avoid common patterns and dictionary words</li>');
        break;
      }
    }
    
    // Advanced pattern checks
    if (advancedEnabled) {
      const advancedIssues = performAdvancedAnalysis(password);
      for (const issue of advancedIssues) {
        feedback.push(`<li class="text-warning">${issue}</li>`);
      }
    }
    
    feedback.push('</ul>');
  } else {
    feedback.push('<div class="alert alert-success mb-0">');
    feedback.push('<i class="fas fa-check-circle me-2"></i>');
    
    if (advancedEnabled) {
      const advancedIssues = performAdvancedAnalysis(password);
      if (advancedIssues.length > 0) {
        feedback.push('Your password is strong, but consider the advanced warnings above.');
      } else {
        feedback.push('Your password is excellent! Passed all advanced security checks.');
      }
    } else {
      feedback.push('Your password is strong!');
    }
    
    feedback.push('</div>');
  }
  
  return feedback.join('');
}

/**
 * Hide password analysis
 */
function hidePasswordAnalysis() {
  document.getElementById('passwordStrengthMeter')?.classList.add('d-none');
  document.getElementById('passwordAnalysis')?.classList.add('d-none');
}

/**
 * Set up password generator functionality
 */
function setupPasswordGenerator() {
  const generateBtn = document.getElementById('generatePasswordBtn');
  const clearHistoryBtn = document.getElementById('clearHistoryBtn');
  if (generateBtn) {
    generateBtn.addEventListener('click', generatePasswords);
  }
  
  if (clearHistoryBtn) {
    clearHistoryBtn.addEventListener('click', clearPasswordHistory);
  }
}

/**
 * Generate secure passwords
 */
function generatePasswords() {
  const lengthEl = document.getElementById('passwordLengthValue') || document.getElementById('passwordLength');
  const countEl = document.getElementById('passwordCount');
  const uppercaseEl = document.getElementById('includeUppercase');
  const lowercaseEl = document.getElementById('includeLowercase');
  const numbersEl = document.getElementById('includeNumbers');
  const symbolsEl = document.getElementById('includeSymbols');
  const excludeEl = document.getElementById('excludeChars');
  
  // Validate all elements exist
  if (!lengthEl || !countEl || !uppercaseEl || !lowercaseEl || !numbersEl || !symbolsEl) {
    console.error('Password generator elements not found in DOM');
    alert('Password generator is not properly initialized. Please try refreshing the page.');
    return;
  }
  
  const length = parseInt(lengthEl.value);
  const count = parseInt(countEl.value);
  const options = {
    uppercase: uppercaseEl.checked,
    lowercase: lowercaseEl.checked,
    numbers: numbersEl.checked,
    symbols: symbolsEl.checked,
    exclude: excludeEl ? excludeEl.value : ''
  };
  
  // Validate options
  if (!options.uppercase && !options.lowercase && !options.numbers && !options.symbols) {
    alert('Please select at least one character type.');
    return;
  }
  
  const passwords = [];
  for (let i = 0; i < count; i++) {
    const password = generateSecurePassword(length, options);
    passwords.push(password);
    passwordHistory.unshift({ password, timestamp: new Date() });
  }
  
  // Keep history limited to 50 entries
  passwordHistory = passwordHistory.slice(0, 50);
  
  displayGeneratedPasswords(passwords);
  updatePasswordHistory();
}

/**
 * Generate a secure password using Web Crypto API
 */
function generateSecurePassword(length, options) {
  let charset = '';
  
  if (options.uppercase) charset += CHAR_SETS.uppercase;
  if (options.lowercase) charset += CHAR_SETS.lowercase;
  if (options.numbers) charset += CHAR_SETS.numbers;
  if (options.symbols) charset += CHAR_SETS.symbols;
  
  // Remove excluded characters
  if (options.exclude) {
    const excludeSet = new Set(options.exclude.split(''));
    charset = charset.split('').filter(char => !excludeSet.has(char)).join('');
  }
  
  if (!charset) return '';
  
  const password = new Uint8Array(length);
  crypto.getRandomValues(password);
  
  let result = '';
  for (let i = 0; i < length; i++) {
    result += charset[password[i] % charset.length];
  }
  
  // Ensure at least one character from each selected type
  if (options.uppercase && !/[A-Z]/.test(result)) {
    result = ensureCharacterType(result, CHAR_SETS.uppercase, charset);
  }
  if (options.lowercase && !/[a-z]/.test(result)) {
    result = ensureCharacterType(result, CHAR_SETS.lowercase, charset);
  }
  if (options.numbers && !/[0-9]/.test(result)) {
    result = ensureCharacterType(result, CHAR_SETS.numbers, charset);
  }
  if (options.symbols && !/[^a-zA-Z0-9]/.test(result)) {
    result = ensureCharacterType(result, CHAR_SETS.symbols, charset);
  }
  
  return result;
}

/**
 * Ensure password contains at least one character from specified type
 */
function ensureCharacterType(password, typeCharset, fullCharset) {
  const randomIndex = crypto.getRandomValues(new Uint8Array(1))[0] % password.length;
  const randomChar = typeCharset[crypto.getRandomValues(new Uint8Array(1))[0] % typeCharset.length];
  
  return password.substring(0, randomIndex) + randomChar + password.substring(randomIndex + 1);
}


/**
 * Display generated passwords
 */
function displayGeneratedPasswords(passwords) {
  const container = document.getElementById('generatedPasswords');
  const list = document.getElementById('passwordList');
  
  if (!container || !list) return;
  
  container.classList.remove('d-none');
  
  list.innerHTML = passwords.map((password, index) => {
    const strength = calculateStrengthScore(password, calculateEntropy(password));
    const level = STRENGTH_LEVELS[strength];
    
    return `
      <div class="list-group-item d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center flex-grow-1">
          <code class="fs-6 me-3">${escapeHtml(password)}</code>
          <span class="badge ${level.class}">${level.label}</span>
        </div>
        <button class="btn btn-sm btn-outline-primary copy-password-btn" data-password="${escapeHtml(password)}">
          <i class="fas fa-copy"></i>
        </button>
      </div>
    `;
  }).join('');
  
  // Add copy event listeners
  list.querySelectorAll('.copy-password-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const password = e.currentTarget.dataset.password;
      copyToClipboard(password, e.currentTarget);
    });
  });
}

/**
 * Update password history display
 */
function updatePasswordHistory() {
  const container = document.getElementById('passwordHistory');
  const list = document.getElementById('historyList');
  
  if (!container || !list || passwordHistory.length === 0) return;
  
  container.classList.remove('d-none');
  
  list.innerHTML = passwordHistory.slice(0, 10).map(entry => {
    const timeAgo = getTimeAgo(entry.timestamp);
    return `
      <div class="list-group-item d-flex justify-content-between align-items-center">
        <div>
          <code class="small">${escapeHtml(entry.password)}</code>
          <div class="text-muted small">${timeAgo}</div>
        </div>
        <button class="btn btn-sm btn-outline-primary copy-password-btn" data-password="${escapeHtml(entry.password)}">
          <i class="fas fa-copy"></i>
        </button>
      </div>
    `;
  }).join('');
  
  // Add copy event listeners
  list.querySelectorAll('.copy-password-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const password = e.currentTarget.dataset.password;
      copyToClipboard(password, e.currentTarget);
    });
  });
}

/**
 * Clear password history
 */
function clearPasswordHistory() {
  passwordHistory = [];
  document.getElementById('passwordHistory')?.classList.add('d-none');
  document.getElementById('historyList').innerHTML = '';
}

/**
 * Copy to clipboard with feedback
 */
async function copyToClipboard(text, button) {
  try {
    await navigator.clipboard.writeText(text);
    
    // Visual feedback
    const originalHtml = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-primary');
    
    setTimeout(() => {
      button.innerHTML = originalHtml;
      button.classList.remove('btn-success');
      button.classList.add('btn-outline-primary');
    }, 2000);
    
    // Clear clipboard after 60 seconds
    setTimeout(() => {
      navigator.clipboard.writeText('');
    }, 60000);
    
  } catch (err) {
    console.error('Failed to copy:', err);
    alert('Failed to copy to clipboard');
  }
}

/**
 * Get time ago string
 */
function getTimeAgo(timestamp) {
  const seconds = Math.floor((new Date() - timestamp) / 1000);
  
  if (seconds < 60) return 'just now';
  if (seconds < 3600) return Math.floor(seconds / 60) + ' min ago';
  if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
  return Math.floor(seconds / 86400) + ' days ago';
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}