import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect() {
    /** @type {HTMLFormElement} */
    this.element;
  }

  async sendVerificationEmail() {
    /** @type {HTMLAnchorElement} */
    const sendVerificationEmailLink = this.element.querySelector(
      "#sendVerificationEmail"
    );

    const originalLink = {
      href: sendVerificationEmailLink.href,
      text: sendVerificationEmailLink.textContent,
    };

    try {
      sendVerificationEmailLink.removeAttribute("href");
      sendVerificationEmailLink.textContent = "sending email...";

      const response = await fetch(originalLink.href);

      const message = await response.text();

      if (!response.ok) {
        throw new Error(message);
      }

      sendVerificationEmailLink.textContent = "email sent";
    } catch (error) {
      sendVerificationEmailLink.href = originalLink.href;
      sendVerificationEmailLink.textContent = originalLink.text;
    }
  }
}
