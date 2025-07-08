# Restaurant Management System - Test Automation Suite

## Overview
This comprehensive test suite automates the complete workflow for testing the Restaurant Management System from login to branch activation. The tests are designed to be:

- **Modular**: Each test unit can run independently
- **Stateful**: Tests remember their progress and skip completed steps
- **Robust**: Enhanced error handling and retry mechanisms
- **Efficient**: No need to re-login or repeat successful operations

## Test Structure

### Test Units
1. **Unit 1**: 🔐 Admin Login
2. **Unit 2**: 📋 Create Subscription Plan
3. **Unit 3**: 🏢 Create Organization  
4. **Unit 4**: 🔑 Extract Organization Activation Key
5. **Unit 5**: 🏪 Activate First Branch
6. **Unit 6**: 🏪 Create New Branch
7. **Unit 7**: 🔑 Activate New Branch
8. **Unit 8**: ✅ Final Verification & Summary

### State Management
- Tests maintain state in `test-state.json`
- Completed steps are automatically skipped on re-run
- Progress tracking with completion percentage
- Error recovery and debugging support

## Usage

### Running All Tests
```bash
# Run the optimized test suite
npx playwright test optimized-test.spec.ts

# Run with browser visible
npx playwright test optimized-test.spec.ts --headed

# Run specific unit
npx playwright test optimized-test.spec.ts -g "Unit 1"
```

### Running Individual Units
```bash
# Login only
npx playwright test optimized-test.spec.ts -g "Admin Login"

# Plan creation only
npx playwright test optimized-test.spec.ts -g "Create Subscription Plan"

# Organization creation only
npx playwright test optimized-test.spec.ts -g "Create Organization"
```

### State Management Commands
```bash
# Check current test state
npx playwright test optimized-test.spec.ts -g "Debug Current State"

# Clear state and restart from beginning
npx playwright test optimized-test.spec.ts -g "Clear Test State"
```

## Test Configuration

### Prerequisites
- Restaurant Management System running at: `http://restaurent-management-sysytem.test`
- Admin credentials: `superadmin@rms.com` / `SuperAdmin123!`
- Playwright installed and configured

### Key Features

#### Enhanced Key Extraction
- Multiple strategies for copying activation keys
- Automatic clipboard handling
- Fallback mechanisms for key detection

#### Smart Navigation
- Intelligent section navigation with retries
- Multiple selector strategies for buttons and links
- Robust page load detection

#### Form Handling
- Safe form filling with validation
- Input verification after filling
- Enhanced error handling

#### Error Recovery
- Automatic screenshots on errors
- Detailed error logging
- State preservation on failures

## Test Data

### Generated Data
- Organization names: `Test-Org-{timestamp}-{random}`
- Branch names: `Test-Branch-{timestamp}-{random}`
- Plan names: `Test-Plan-{timestamp}-{random}`
- Emails: `test{timestamp}@example.com`

### Fixed Data
- Phone: `0712345678`
- Password: `Password@123`
- Standard addresses and contact information

## Output & Reporting

### Console Output
- Real-time progress indicators
- Step-by-step status updates
- Completion percentage tracking
- Final summary with timing

### Debug Information
- Error screenshots saved as `debug-{step}-{timestamp}.png`
- Detailed state logging
- Network activity monitoring

### State File
```json
{
  "loginCompleted": true,
  "planCreated": true,
  "organizationCreated": true,
  "organizationActivated": true,
  "branchesActivated": true,
  "newBranchCreated": true,
  "newBranchActivated": true,
  "planName": "Test-Plan-1704067200000-abc123",
  "organizationName": "Test-Org-1704067200000-def456",
  "newBranchName": "Test-Branch-1704067200000-ghi789",
  "testStartTime": "2024-01-01T00:00:00.000Z",
  "lastUpdateTime": "2024-01-01T00:10:00.000Z"
}
```

## Troubleshooting

### Common Issues

#### Test Fails at Login
- Verify the application is running
- Check admin credentials
- Ensure network connectivity

#### Key Extraction Fails
- Check if activation keys are properly displayed
- Verify copy button functionality
- Run with `--headed` to see browser interaction

#### State Issues
- Clear state with debug command if needed
- Check `test-state.json` for corruption
- Restart from specific unit if needed

### Recovery Steps
1. Run debug state command to check current progress
2. Identify failed step from console output
3. Clear state if needed and restart
4. Use `--headed` mode for visual debugging

## Advanced Usage

### Custom Configuration
Modify the following constants in the test files:
- `BASE_URL`: Change application URL
- `STATE_FILE`: Change state file location
- Credentials: Update in login unit

### Extending Tests
Add new test units by:
1. Creating new test function
2. Adding state tracking fields
3. Implementing skip logic
4. Adding to verification unit

### Integration
- Can be integrated into CI/CD pipelines
- Supports parallel execution (with separate state files)
- Compatible with Playwright reporting tools
