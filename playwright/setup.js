#!/usr/bin/env node

/**
 * Restaurant Management System Test Setup Script
 * Helps users quickly set up and run the comprehensive test suite
 */

import { execSync } from 'child_process';
import { promises as fs } from 'fs';
import path from 'path';

const colors = {
  green: '\x1b[32m',
  red: '\x1b[31m',
  yellow: '\x1b[33m',
  blue: '\x1b[34m',
  reset: '\x1b[0m',
  bold: '\x1b[1m'
};

function log(message, color = 'reset') {
  console.log(`${colors[color]}${message}${colors.reset}`);
}

function execCommand(command, description) {
  try {
    log(`🔄 ${description}...`, 'blue');
    execSync(command, { stdio: 'inherit' });
    log(`✅ ${description} completed`, 'green');
    return true;
  } catch (error) {
    log(`❌ ${description} failed`, 'red');
    return false;
  }
}

async function checkTestState() {
  try {
    const stateFile = await fs.readFile('test-state.json', 'utf8');
    const state = JSON.parse(stateFile);

    log('\n📊 Current Test State:', 'blue');

    const steps = [
      { key: 'loginCompleted', name: '🔐 Admin Login' },
      { key: 'planCreated', name: '📋 Subscription Plan Created' },
      { key: 'organizationCreated', name: '🏢 Organization Created' },
      { key: 'organizationActivated', name: '🔑 Organization Key Extracted' },
      { key: 'branchesActivated', name: '🏪 First Branch Activated' },
      { key: 'newBranchCreated', name: '🏪 New Branch Created' },
      { key: 'newBranchActivated', name: '🔑 New Branch Activated' }
    ];

    steps.forEach(step => {
      const status = state[step.key] ? '✅' : '⏳';
      log(`   ${status} ${step.name}`);
    });

    const completed = steps.filter(step => state[step.key]).length;
    const percentage = Math.round((completed / steps.length) * 100);

    log(`\n🎯 Progress: ${completed}/${steps.length} (${percentage}%)`, 'bold');

    if (state.testStartTime) {
      log(`⏰ Started: ${new Date(state.testStartTime).toLocaleString()}`);
    }

    return state;
  } catch (error) {
    log('📝 No existing test state found', 'yellow');
    return null;
  }
}

async function main() {
  log('🚀 Restaurant Management System Test Setup', 'bold');
  log('=' .repeat(50), 'blue');

  // Check if we're in the right directory
  try {
    await fs.access('playwright.config.js');
    await fs.access('playwright');
  } catch (error) {
    log('❌ Error: Please run this script from the project root directory', 'red');
    process.exit(1);
  }

  // Check current test state
  const currentState = await checkTestState();

  // Show menu
  log('\n📋 Available Commands:', 'blue');
  log('1. npm run test:optimized     - Run full optimized test suite');
  log('2. npm run test:headed        - Run tests with browser visible');
  log('3. npm run test:debug         - Run tests in debug mode');
  log('4. npm run test:status        - Check current test progress');
  log('5. npm run test:clear         - Clear test state and restart');
  log('6. npm run test:login         - Run login test only');
  log('7. npm run test:plan          - Run plan creation test only');
  log('8. npm run test:org           - Run organization creation test only');
  log('9. npm run test:branches      - Run branch tests only');
  log('10. npm run test:report       - View test report');

  log('\n🎯 Quick Start:', 'green');
  if (currentState && Object.values(currentState).some(v => v === true)) {
    log('   Continue from where you left off:');
    log('   npm run test:optimized', 'bold');
  } else {
    log('   Start fresh test run:');
    log('   npm run test:optimized', 'bold');
  }

  log('\n🔧 Prerequisites:', 'yellow');
  log('   • Restaurant Management System running at:');
  log('     http://restaurent-management-sysytem.test', 'bold');
  log('   • Admin credentials: superadmin@rms.com / SuperAdmin123!');

  log('\n📖 For detailed information, see: playwright/TEST-README.md', 'blue');

  // Auto-run if argument provided
  const arg = process.argv[2];
  if (arg) {
    switch (arg) {
      case 'run':
      case 'start':
        log('\n🎬 Starting optimized test suite...', 'green');
        execCommand('npm run test:optimized', 'Running tests');
        break;
      case 'status':
        // Already shown above
        break;
      case 'clear':
        try {
          await fs.unlink('test-state.json');
          log('\n🗑️ Test state cleared successfully', 'green');
        } catch (error) {
          log('\n⚠️ No test state to clear', 'yellow');
        }
        break;
      case 'headed':
        log('\n🎬 Starting tests with browser visible...', 'green');
        execCommand('npm run test:headed', 'Running tests with browser');
        break;
      default:
        log(`\n❌ Unknown command: ${arg}`, 'red');
        log('Available: run, status, clear, headed', 'yellow');
    }
  }
}

// Handle errors gracefully
process.on('uncaughtException', (error) => {
  log(`\n❌ Unexpected error: ${error.message}`, 'red');
  process.exit(1);
});

main().catch(error => {
  log(`\n❌ Setup error: ${error.message}`, 'red');
  process.exit(1);
});
