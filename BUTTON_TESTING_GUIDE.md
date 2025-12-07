# Testing Guide - Manager Dashboard Buttons

## Quick Start Testing

### 1. Access Manager Dashboard
```
URL: http://localhost/CHAKANOKS-1/dashboard
Login: Use manager role credentials
Expected: Manager dashboard loads with 3 action buttons
```

### 2. Test Each Button

#### Test 1: Manage Orders Button
```
ACTION: Click "📦 Manage Orders" button
EXPECTED FLOW:
  1. Confirmation modal appears
     - Title: "Manage Orders"
     - Message: "You will be redirected to view all your orders. Continue?"
  2. Two buttons appear: Cancel and Confirm
  3. Click "Confirm"
  4. Loading spinner shows on button
  5. Page redirects to /order (Order Management page)
  
VERIFY:
  ✓ Modal appears correctly
  ✓ Modal message is clear
  ✓ Buttons are clickable
  ✓ Navigation works smoothly
  ✓ Loading state visible
```

#### Test 2: Create New Order Button
```
ACTION: Click "➕ Create New Order" button
EXPECTED FLOW:
  1. Confirmation modal appears
     - Title: "Create New Order"
     - Message: "Start creating a new supply order? You can save it as draft."
  2. Click "Confirm"
  3. Loading spinner shows
  4. Page redirects to /order/create (Order Creation form)

VERIFY:
  ✓ Correct confirmation message
  ✓ Navigation to creation form works
  ✓ Form loads successfully
```

#### Test 3: Pending Approval Button
```
ACTION: Click "⏳ Pending Approval" button
EXPECTED FLOW:
  1. Confirmation modal appears
     - Title: "View Pending Orders"
     - Message: "View all orders awaiting admin approval?"
  2. Click "Confirm"
  3. Page redirects to /order/pending

VERIFY:
  ✓ Pending orders displayed
  ✓ Correct orders shown
```

## Modal Interaction Testing

### Close Button (×) Test
```
ACTION: 
  1. Click any action button
  2. When modal opens, click × button in top-right
  
EXPECTED:
  ✓ Modal closes smoothly
  ✓ No navigation occurs
  ✓ Page remains unchanged
```

### Cancel Button Test
```
ACTION:
  1. Click any action button
  2. When modal opens, click "Cancel" button
  
EXPECTED:
  ✓ Modal closes
  ✓ No navigation
  ✓ Returns to dashboard
```

### Click Outside Modal Test
```
ACTION:
  1. Click any action button
  2. When modal opens, click on gray area outside modal
  
EXPECTED:
  ✓ Modal closes (if implemented)
  ✓ No navigation
  ✓ Smooth transition
```

### Escape Key Test
```
ACTION:
  1. Click any action button
  2. Press Escape key on keyboard
  
EXPECTED:
  ✓ Modal closes immediately
  ✓ No navigation
```

## Alert Message Testing

### Success Alert Test
```
ACTION: Open browser console and run:
showAlert('Test success message', 'success');

EXPECTED:
  ✓ Green alert appears at top
  ✓ Shows success icon/styling
  ✓ Auto-dismisses after 5 seconds
  ✓ Has close × button (manual dismiss)
```

### Error Alert Test
```
ACTION: Open browser console and run:
showAlert('Test error message', 'error');

EXPECTED:
  ✓ Red alert appears
  ✓ Shows error styling
  ✓ Auto-dismisses after 5 seconds
```

### Info Alert Test
```
ACTION: Open browser console and run:
showAlert('Test info message', 'info');

EXPECTED:
  ✓ Blue alert appears
  ✓ Shows info styling
  ✓ Auto-dismisses after 5 seconds
```

## Button State Testing

### Hover State
```
ACTION: Move mouse over any action button
EXPECTED:
  ✓ Button lifts up (transform: translateY)
  ✓ Shadow increases
  ✓ Smooth transition (0.3s)
  ✓ Cursor changes to pointer
```

### Active/Click State
```
ACTION: Click action button
EXPECTED:
  ✓ Modal appears within 0.3s
  ✓ Button visual state maintained
  ✓ Loading spinner visible when confirming
```

## Dynamic Content Testing

### Order Count Badges
```
VERIFY:
  ✓ "Manage Orders" shows correct total count
  ✓ "Create New Order" shows draft count
  ✓ "Pending Approval" shows pending count
  
HOW TO CHECK:
  1. Open Dashboard
  2. Observe badge numbers
  3. Create new order (saves as draft)
  4. Return to dashboard
  5. Draft count should increase by 1
```

## Responsive Design Testing

### Desktop View
```
RESOLUTION: 1920x1080
EXPECTED:
  ✓ All 3 buttons display in single row
  ✓ Buttons evenly spaced
  ✓ Text readable
  ✓ Modal centered on screen
```

### Tablet View
```
RESOLUTION: 768x1024
EXPECTED:
  ✓ Buttons stack to 2 per row
  ✓ Modal still centered
  ✓ Responsive layout works
  ✓ Touch-friendly button size
```

### Mobile View
```
RESOLUTION: 375x667
EXPECTED:
  ✓ Buttons stack to 1 per row
  ✓ Full width with padding
  ✓ Modal responsive
  ✓ Touch interactions work
```

## Browser Compatibility Testing

```
Test these browsers:
- Google Chrome (v90+)
- Firefox (v88+)
- Safari (v14+)
- Edge (v90+)

EXPECTED: All features work identically across browsers
```

## Performance Testing

### Modal Load Time
```
TEST: Time from click to modal visible
EXPECTED: < 100ms
MEASURE: Open DevTools → Performance tab → Click button
```

### Page Navigation Speed
```
TEST: Time from confirmation to page load
EXPECTED: < 1 second for navigation
MEASURE: Use browser's Network/Timing tab
```

### Memory Usage
```
TEST: Open/close modal 20 times
EXPECTED: No significant memory increase
MEASURE: DevTools → Memory tab
```

## Edge Cases & Error Handling

### Test 1: Rapid Button Clicks
```
ACTION: Click button multiple times rapidly
EXPECTED:
  ✓ Only one modal opens
  ✓ No duplicate modals
  ✓ No navigation lag
```

### Test 2: Network Delay
```
ACTION: 
  1. Open DevTools Network tab
  2. Set to "Slow 3G"
  3. Click action button
  
EXPECTED:
  ✓ Modal still opens immediately
  ✓ Loading spinner shows during delay
  ✓ Navigation eventually completes
```

### Test 3: JavaScript Disabled
```
ACTION: Disable JavaScript in browser
EXPECTED:
  ✓ Buttons visible
  ✓ Clear fallback (basic link navigation)
  ✓ No console errors
```

## Console Testing

### Check for JavaScript Errors
```
ACTION: Open browser DevTools (F12) → Console tab
EXPECTED:
  ✓ No red error messages
  ✓ No warning messages related to modals
  ✓ Info message: "Manager Dashboard initialized successfully"
```

### Debug Modal State
```
IN CONSOLE RUN:
console.log(document.getElementById('confirmModal').classList);

EXPECTED:
  [When closed] DOMTokenList [ 'modal' ]
  [When open]   DOMTokenList [ 'modal', 'show' ]
```

### Test Alert Container
```
IN CONSOLE RUN:
document.getElementById('alertContainer').innerHTML;

EXPECTED:
  [Shows current alerts]
  [Empty when no alerts active]
```

## Integration Testing

### Test Full Workflow: Create Order
```
WORKFLOW:
  1. Click "Create New Order"
  2. Confirm in modal
  3. Fill order form
  4. Submit order
  5. Receive success message (backend)
  
EXPECTED:
  ✓ All steps complete smoothly
  ✓ Order appears in "Manage Orders"
  ✓ Dashboard count updates
```

### Test Full Workflow: View Orders
```
WORKFLOW:
  1. Click "Manage Orders"
  2. Confirm in modal
  3. View order list
  4. Click on an order
  5. Edit and save
  
EXPECTED:
  ✓ Orders display correctly
  ✓ Edits save successfully
  ✓ Return to dashboard works
```

### Test Full Workflow: Check Pending
```
WORKFLOW:
  1. Create order and submit for approval
  2. Return to dashboard
  3. Click "Pending Approval"
  4. Confirm modal
  5. View pending orders
  
EXPECTED:
  ✓ Recently submitted order appears in pending
  ✓ Status shows "Pending Approval"
```

## Automated Testing (Optional)

### Cypress Test Example
```javascript
describe('Manager Dashboard Buttons', () => {
  beforeEach(() => {
    cy.visit('/dashboard');
  });

  it('should show confirmation modal on button click', () => {
    cy.contains('Manage Orders').click();
    cy.get('#confirmModal').should('have.class', 'show');
  });

  it('should navigate on confirmation', () => {
    cy.contains('Manage Orders').click();
    cy.get('.btn-primary').contains('Confirm').click();
    cy.url().should('include', '/order');
  });

  it('should close modal on cancel', () => {
    cy.contains('Create New Order').click();
    cy.contains('Cancel').click();
    cy.get('#confirmModal').should('not.have.class', 'show');
  });
});
```

## Success Criteria Checklist

```
□ All 3 buttons functional
□ Modals appear with correct content
□ Modal controls work (confirm, cancel, close)
□ Page navigation works
□ Loading states visible
□ Alert messages display
□ Keyboard shortcuts work (Escape)
□ Responsive on all devices
□ No console errors
□ Performance acceptable
□ Cross-browser compatible
□ Edge cases handled
□ User experience smooth
```

## Common Issues & Solutions

### Issue: Modal not appearing
**Solution:** Check browser console for errors. Verify JavaScript is enabled.

### Issue: Button click unresponsive
**Solution:** Check that onclick attribute matches function name exactly. Verify JavaScript loaded.

### Issue: Modal appearing but not closing
**Solution:** Verify closeModal() function exists. Check modal ID matches.

### Issue: Navigation slow
**Solution:** Check network conditions. May need to optimize backend response time.

### Issue: Multiple alerts stacking
**Solution:** This is expected behavior. They auto-dismiss after 5 seconds.

## Support & Debugging

### Enable Debug Mode
```javascript
// Add to console
localStorage.setItem('debug_dashboard', 'true');
// Reload page
```

### Check Initialization
```javascript
// In console, should log successfully
console.log('Dashboard initialized');
```

### Test Modal Functions
```javascript
// Open modal manually
openModal('confirmModal');

// Close modal manually
closeModal('confirmModal');

// Show alert
showAlert('Test message', 'success');
```
