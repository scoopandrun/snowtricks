import { Controller } from "@hotwired/stimulus";
import Quill from "quill";

export default class extends Controller {
  connect() {
    /** @type {HTMLFormElement} */
    const form = this.element;

    // Hide the textarea element
    /** @type {HTMLTextAreaElement} */
    const descriptionTextarea = form.querySelector("#trick_description");
    if (descriptionTextarea) {
      descriptionTextarea.setAttribute("hidden", true);
    }

    const quillEditor = new Quill("#quill-editor", {
      modules: {
        toolbar: [
          ["bold", "italic", "underline", "strike"], // toggled buttons
          ["blockquote", "code-block"],

          [{ list: "ordered" }, { list: "bullet" }],
          [{ script: "sub" }, { script: "super" }], // superscript/subscript
          [{ indent: "-1" }, { indent: "+1" }], // outdent/indent
          [{ direction: "rtl" }], // text direction

          [{ size: ["small", false, "large", "huge"] }], // custom dropdown
          [{ header: [1, 2, 3, 4, 5, 6, false] }],

          [{ color: [] }, { background: [] }], // dropdown with defaults from theme
          [{ font: [] }],
          [{ align: [] }],

          ["clean"], // remove formatting button
        ],
      },
      placeholder: "Trick description...",
      theme: "snow",
    });

    try {
      quillEditor.setContents(JSON.parse(descriptionTextarea.textContent));
    } catch (error) {
      quillEditor.setText(descriptionTextarea.textContent);
    }

    form.addEventListener("submit", (e) => {
      descriptionTextarea.textContent = JSON.stringify(
        quillEditor.getContents()
      );
    });
  }
}
