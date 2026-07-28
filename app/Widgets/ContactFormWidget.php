<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\ContactForm;
use App\Models\WidgetInstance;

class ContactFormWidget
{
    public function render(WidgetInstance $instance): string
    {
        $config = $instance->config ?? [];
        $formId = $config['form_id'] ?? null;
        $title = $instance->title ?: '';

        if (!$formId) {
            return '<!-- ContactFormWidget: No form selected -->';
        }

        $form = ContactForm::find($formId);

        if (!$form || !$form->is_active) {
            return '<!-- ContactFormWidget: Form not found or inactive -->';
        }

        $uniqueId = 'contact-form-' . $form->id . '-' . uniqid();
        $fields = $form->fields ?? [];

        $html = '<div class="widget widget-contact-form" style="width: 100%; position: relative;">';

        if ($title) {
            $html .= '<h3 class="widget-title" style="font-size: 1.1rem; font-weight: 600; color: var(--geist-foreground); margin-bottom: 1.25rem;">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h3>';
        }

        $isInline = ($form->type === 'newsletter');
        $formStyle = $isInline 
            ? 'display: flex; align-items: flex-end; gap: 0.5rem; width: 100%;' 
            : 'display: flex; flex-direction: column; gap: 0.75rem;';

        $html .= '<form id="' . $uniqueId . '" class="contact-form-widget-el" style="' . $formStyle . '">';
        $html .= '<input type="hidden" name="form_id" value="' . $form->id . '">';

        foreach ($fields as $field) {
            $name = $field['name'] ?? '';
            $type = $field['type'] ?? 'text';
            $label = $field['label'] ?? '';
            $required = !empty($field['required']) ? 'required' : '';
            $placeholder = $field['placeholder'] ?? '';

            if (empty($name)) {
                continue;
            }

            $groupStyle = $isInline 
                ? 'display: flex; flex-direction: column; gap: 0.25rem; flex: 1; min-width: 0; position: relative;' 
                : 'display: flex; flex-direction: column; gap: 0.25rem;';

            $html .= '<div class="form-group" style="' . $groupStyle . '">';
            if ($type !== 'checkbox') {
                $labelStyle = $isInline 
                    ? 'font-size: 0.8rem; font-weight: 500; color: var(--geist-accents-5); white-space: nowrap; text-overflow: ellipsis; overflow: hidden; display: block;'
                    : 'font-size: 0.8rem; font-weight: 500; color: var(--geist-accents-5); display: block;';
                $html .= '<label style="' . $labelStyle . '">' . e($label) . ($required ? ' <span style="color: var(--geist-error); font-size: 0.7rem;">*</span>' : '') . '</label>';
            }

            $inputStyle = 'width: 100%; height: 42px; padding: 0.6rem 0.8rem; border-radius: 8px; border: 1px solid var(--geist-accents-2); background: var(--geist-background); color: var(--geist-foreground); font-size: 0.85rem; outline: none; transition: border-color 0.2s; box-sizing: border-box;';

            if ($type === 'textarea') {
                $html .= '<textarea name="' . e($name) . '" ' . $required . ' placeholder="' . e($placeholder) . '" rows="4" style="' . $inputStyle . '" onfocus="this.style.borderColor=\'var(--geist-foreground)\'" onblur="this.style.borderColor=\'var(--geist-accents-2)\'"></textarea>';
            } elseif ($type === 'checkbox') {
                $html .= '<label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--geist-accents-5); cursor: pointer;">';
                $html .= '<input type="checkbox" name="' . e($name) . '" value="1" ' . $required . ' style="cursor: pointer; border-radius: 4px; border: 1px solid var(--geist-accents-2);">';
                $html .= '<span>' . e($label) . ($required ? ' <span style="color: var(--geist-error); font-size: 0.7rem;">*</span>' : '') . '</span>';
                $html .= '</label>';
            } else {
                $html .= '<input type="' . e($type) . '" name="' . e($name) . '" ' . $required . ' placeholder="' . e($placeholder) . '" style="' . $inputStyle . '" onfocus="this.style.borderColor=\'var(--geist-foreground)\'" onblur="this.style.borderColor=\'var(--geist-accents-2)\'">';
            }
            
            $feedbackErrorStyle = $isInline
                ? 'display: none; color: #ef4444; font-size: 0.75rem; position: absolute; top: 100%; left: 0; right: 0; z-index: 99; margin-top: 0.25rem; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;'
                : 'display: none; color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;';
            $html .= '<span class="invalid-feedback" style="' . $feedbackErrorStyle . '"></span>';
            $html .= '</div>';
        }

        if (!$isInline && get_option('contacts_recaptcha_enabled')) {
            $siteKey = get_option('contacts_recaptcha_site_key');
            if (!empty($siteKey)) {
                $html .= '<div class="form-group recaptcha-container" style="margin: 0.5rem 0 1rem 0; min-height: 78px; position: relative;">';
                $html .= '<div class="g-recaptcha" data-sitekey="' . e($siteKey) . '"></div>';
                $html .= '<span class="invalid-feedback" style="display: none; color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;"></span>';
                $html .= '</div>';
                $html .= '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
            }
        }

        $btnStyle = $isInline
            ? 'padding: 0 1.25rem; border-radius: 8px; border: none; background: var(--geist-foreground); color: var(--geist-background); font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: opacity 0.2s; display: flex; align-items: center; justify-content: center; height: 42px; white-space: nowrap; box-sizing: border-box;'
            : 'align-self: flex-start; padding: 0.65rem 2rem; border-radius: 8px; border: none; background: var(--geist-foreground); color: var(--geist-background); font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: opacity 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;';

        $html .= '<button type="submit" class="submit-btn" style="' . $btnStyle . '" onmouseover="this.style.opacity=\'0.9\'" onmouseout="this.style.opacity=\'1\'">';
        $html .= '<span>Submit</span>';
        $html .= '</button>';

        $feedbackStyle = $isInline
            ? 'display: none; font-size: 0.85rem; position: absolute; top: 100%; left: 0; right: 0; z-index: 99; margin-top: 0.5rem; padding: 0.5rem 0.75rem; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'
            : 'display: none; font-size: 0.85rem; margin-top: 0.5rem; padding: 0.5rem 0.75rem; border-radius: 6px;';
        $html .= '<div class="form-feedback" style="' . $feedbackStyle . '"></div>';
        $html .= '</form>';

        // AJAX handling script
        $html .= '<script>
        (function() {
            var form = document.getElementById("' . $uniqueId . '");
            if (!form) return;
            
            // Register input clear handlers
            form.querySelectorAll(".form-group input, .form-group textarea").forEach(function(el) {
                el.addEventListener("input", function() {
                    el.style.borderColor = "var(--geist-accents-2)";
                    var errSpan = el.parentNode.querySelector(".invalid-feedback");
                    if (errSpan) {
                        errSpan.style.display = "none";
                    }
                });
            });

            form.addEventListener("submit", function(e) {
                e.preventDefault();
                var btn = form.querySelector(".submit-btn");
                var feedback = form.querySelector(".form-feedback");
                var btnText = btn.querySelector("span");
                
                btn.disabled = true;
                btn.style.opacity = "0.7";
                btnText.textContent = "Sending...";
                feedback.style.display = "none";
                
                // Clear validation errors
                form.querySelectorAll(".form-group input, .form-group textarea").forEach(function(el) {
                    el.style.borderColor = "var(--geist-accents-2)";
                    var errSpan = el.parentNode.querySelector(".invalid-feedback");
                    if (errSpan) {
                        errSpan.style.display = "none";
                        errSpan.textContent = "";
                    }
                });
                var recaptchaErr = form.querySelector(".recaptcha-container .invalid-feedback");
                if (recaptchaErr) {
                    recaptchaErr.style.display = "none";
                    recaptchaErr.textContent = "";
                }

                var formData = new FormData(form);
                var data = {};
                formData.forEach(function(value, key) {
                    data[key] = value;
                });
                
                fetch("/api/v1/public/contacts/submit", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify(data)
                })
                .then(function(response) {
                    return response.json().then(function(json) {
                        return { status: response.status, ok: response.ok, data: json };
                    });
                })
                .then(function(res) {
                    btn.disabled = false;
                    btn.style.opacity = "1";
                    btnText.textContent = "Submit";
                    
                    if (res.ok) {
                        feedback.style.display = "block";
                        feedback.style.background = "rgba(16, 185, 129, 0.1)";
                        feedback.style.color = "#10b981";
                        feedback.style.border = "1px solid rgba(16, 185, 129, 0.2)";
                        feedback.textContent = res.data.message || "Thank you! Your submission has been received.";
                        form.reset();
                        if (typeof grecaptcha !== "undefined") {
                            grecaptcha.reset();
                        }
                        
                        setTimeout(function() {
                            feedback.style.display = "none";
                        }, 5000);
                    } else {
                        if (res.data.errors) {
                            for (var key in res.data.errors) {
                                if (key === "g-recaptcha-response") {
                                    var container = form.querySelector(".recaptcha-container");
                                    if (container) {
                                        var errSpan = container.querySelector(".invalid-feedback");
                                        if (errSpan) {
                                            errSpan.style.display = "block";
                                            errSpan.textContent = res.data.errors[key].join(" ");
                                        }
                                    }
                                    continue;
                                }
                                var inputEl = form.querySelector("[name=\'" + key + "\']");
                                if (inputEl) {
                                    inputEl.style.borderColor = "#ef4444";
                                    var errSpan = inputEl.parentNode.querySelector(".invalid-feedback");
                                    if (errSpan) {
                                        errSpan.style.display = "block";
                                        errSpan.textContent = res.data.errors[key].join(" ");
                                    }
                                }
                            }
                            
                            setTimeout(function() {
                                form.querySelectorAll(".form-group input, .form-group textarea").forEach(function(el) {
                                    el.style.borderColor = "var(--geist-accents-2)";
                                    var errSpan = el.parentNode.querySelector(".invalid-feedback");
                                    if (errSpan) {
                                        errSpan.style.display = "none";
                                    }
                                });
                                var recaptchaErr = form.querySelector(".recaptcha-container .invalid-feedback");
                                if (recaptchaErr) {
                                    recaptchaErr.style.display = "none";
                                }
                            }, 5000);
                        } else {
                            feedback.style.display = "block";
                            feedback.style.background = "rgba(239, 68, 68, 0.1)";
                            feedback.style.color = "#ef4444";
                            feedback.style.border = "1px solid rgba(239, 68, 68, 0.2)";
                            feedback.textContent = res.data.message || "An error occurred. Please try again.";
                            
                            setTimeout(function() {
                                feedback.style.display = "none";
                            }, 5000);
                        }
                    }
                })
                .catch(function(err) {
                    btn.disabled = false;
                    btn.style.opacity = "1";
                    btnText.textContent = "Submit";
                    feedback.style.display = "block";
                    feedback.style.background = "rgba(239, 68, 68, 0.1)";
                    feedback.style.color = "#ef4444";
                    feedback.style.border = "1px solid rgba(239, 68, 68, 0.2)";
                    feedback.textContent = "Connection error. Please try again.";
                });
            });
        })();
        </script>';

        $html .= '</div>';

        return $html;
    }
}
