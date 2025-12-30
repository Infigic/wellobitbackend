<?php

namespace App\Traits;

trait CmsPageContentHelper
{
    public static function generateTermsContent()
    {
        return '<h2>Terms & Conditions</h2>
                <p>Welcome to our website. By accessing and using this website, you accept and agree to be bound by the terms and provisions of this agreement.</p>
                <h3>1. User Responsibilities</h3>
                <p>As a user of the website, you agree not to:</p>
                <ul>
                    <li>Misuse or hack any part of the website</li>
                    <li>Post or transmit unlawful material</li>
                    <li>Violate any applicable laws</li>
                </ul>
                <h3>2. Intellectual Property</h3>
                <p>All content included on this website is the property of our company and protected by copyright laws.</p>';
    }

    public static function generatePrivacyContent()
    {
        return '<h2>Privacy Policy</h2>
                <p>We are committed to protecting your privacy. This policy explains how we collect, use, and safeguard your personal information.</p>
                <h3>1. Information Collection</h3>
                <p>We may collect the following information:</p>
                <ul>
                    <li>Name and contact information</li>
                    <li>Demographic information</li>
                    <li>Other information relevant to customer surveys</li>
                </ul>
                <h3>2. Use of Information</h3>
                <p>We require this information to understand your needs and provide you with better service.</p>';
    }

    public static function generateFaqContent()
    {
        return '<h2>Frequently Asked Questions</h2>
                <div class="faq-item">
                    <h3>How do I create an account?</h3>
                    <p>Click on the "Sign Up" button and follow the registration process.</p>
                </div>
                <div class="faq-item">
                    <h3>How can I reset my password?</h3>
                    <p>Go to the login page and click "Forgot Password" to receive reset instructions.</p>
                </div>
                <div class="faq-item">
                    <h3>What payment methods do you accept?</h3>
                    <p>We accept all major credit cards and PayPal.</p>
                </div>';
    }

    public static function generateHowItWorksContent()
    {
        return '<h2>How It Works</h2>
                <div class="step">
                    <h3>Step 1: Sign Up</h3>
                    <p>Create your account in just a few minutes.</p>
                </div>
                <div class="step">
                    <h3>Step 2: Choose a Plan</h3>
                    <p>Select the plan that best fits your needs.</p>
                </div>
                <div class="step">
                    <h3>Step 3: Get Started</h3>
                    <p>Start using our service immediately after payment.</p>
                </div>';
    }
}
