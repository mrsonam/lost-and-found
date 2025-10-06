document.addEventListener('DOMContentLoaded', function () {
  function getFormGroup(element) {
    let parent = element.parentElement;
    while (parent && !parent.classList.contains('form-group')) {
      parent = parent.parentElement;
    }
    return parent || element.parentElement;
  }

  function setErrorState(element, hasError) {
    const group = getFormGroup(element);
    if (!group) return;
    const label = group.querySelector('label');
    let errorMsg = group.querySelector('.field-error-message');
    if (!errorMsg) {
      // Create an error message container if it doesn't exist
      errorMsg = document.createElement('span');
      errorMsg.className = 'field-error-message';
      errorMsg.style.display = 'none';
      // Insert after label if present, else append to group
      if (label && label.nextSibling) {
        label.parentNode.insertBefore(errorMsg, label.nextSibling);
      } else if (label) {
        label.parentNode.appendChild(errorMsg);
      } else {
        group.appendChild(errorMsg);
      }
    }

    if (hasError) {
      group.classList.add('has-error');
      element.classList.add('field-error');
      if (label) label.classList.add('label-error');
      if (errorMsg) {
        // If no message present, provide a generic, context-aware one
        if (!errorMsg.textContent || errorMsg.textContent.trim() === '') {
          errorMsg.textContent = getErrorMessage(element);
        }
        errorMsg.style.display = 'block';
      }
    } else {
      group.classList.remove('has-error');
      element.classList.remove('field-error');
      if (label) label.classList.remove('label-error');
      if (errorMsg) {
        errorMsg.textContent = '';
        errorMsg.style.display = 'none';
      }
    }
  }

  function isFieldValid(element) {
    const type = (element.getAttribute('type') || '').toLowerCase();
    const tag = element.tagName.toLowerCase();

    // File inputs: valid if any file selected
    if (type === 'file') {
      return element.files && element.files.length > 0;
    }

    // Selects: valid if non-empty value
    if (tag === 'select') {
      return element.value.trim() !== '';
    }

    // Email: simple pattern
    if (type === 'email') {
      const value = element.value.trim();
      if (value === '') return false;
      const re = /.+@.+\..+/;
      return re.test(value);
    }

    // Text, password, date, textarea: non-empty
    return element.value && element.value.trim() !== '';
  }

  function handleValidate(e) {
    const el = e.target;
    // Only handle inside floating-form to avoid unintended elements
    if (!el.closest('.floating-form')) return;
    const valid = isFieldValid(el);
    setErrorState(el, !valid);
    if (!valid) {
      const group = getFormGroup(el);
      const errorMsg = group && group.querySelector('.field-error-message');
      if (errorMsg && (!errorMsg.textContent || errorMsg.textContent.trim() === '')) {
        errorMsg.textContent = getErrorMessage(el);
        errorMsg.style.display = 'block';
      }
    }
  }

  function getErrorMessage(element) {
    const type = (element.getAttribute('type') || '').toLowerCase();
    const tag = element.tagName.toLowerCase();
    const name = (element.getAttribute('name') || '').toLowerCase();

    if (type === 'email') return 'Please enter a valid email address.';
    if (type === 'password') return 'Please enter your password.';
    if (type === 'file') return 'Please select a file to upload.';
    if (tag === 'select') return 'Please choose an option.';
    if (type === 'date' || name.includes('date')) return 'Please select a date.';
    if (name.includes('title') || name.includes('name')) return 'This field is required.';
    if (name.includes('description')) return 'Please add a brief description.';
    if (name.includes('location')) return 'Please enter a location.';
    if (name.includes('category')) return 'Please select a category.';
    if (name.includes('contact')) return 'Please choose a contact method.';
    if (name.includes('otp')) return 'Please enter the 6-digit code.';
    return 'Please fill out this field.';
  }

  // Attach listeners to inputs, textareas, selects
  const selectors = ['.floating-form input', '.floating-form textarea', '.floating-form select'];
  const fields = document.querySelectorAll(selectors.join(','));

  fields.forEach(function (el) {
    // Initial pass: if prefilled (server-side old values), clear error
    if (isFieldValid(el)) {
      setErrorState(el, false);
    }

    // Live validation
    el.addEventListener('input', handleValidate);
    el.addEventListener('change', handleValidate);
    el.addEventListener('blur', handleValidate);
  });
});


