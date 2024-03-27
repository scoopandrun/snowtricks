import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["profilePictureInput", "profilePiturePreview"];

  async sendVerificationEmail() {
    /** @type {HTMLFormElement} */
    this.element;

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

  showProfilePicturePreview() {
    /** @type {HTMLInputElement} */
    const fileInput = this.profilePictureInputTarget;

    /** @type {HTMLImageElement} */
    const picturePreview = this.profilePiturePreviewTarget;

    console.log({ fileInput, picturePreview });

    const file = fileInput.files[0];

    if (!file.type.startsWith("image/")) return;

    const reader = new FileReader();
    reader.onload = () => {
      const result = reader.result;

      picturePreview.src = result;
    };
    reader.readAsDataURL(file);
  }
}
