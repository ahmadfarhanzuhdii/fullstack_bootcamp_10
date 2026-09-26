const projects = [
  {
    title: "COMPFEST 17 Fakultas UI",
    description: "Responsive event website focused on clear information hierarchy, engaging visual design and reusable Bootstrap components.",
    tags: ["Bootstrap", "HTML", "CSS", "JavaScript", "Responsive"]
  },
  {
    title: "Creative Dashboard",
    description: "A colorful dashboard concept for presenting analytics in compact cards, charts and responsive layouts.",
    tags: ["Dashboard", "UI/UX", "Bootstrap", "Grid"]
  },
  {
    title: "Creative Studio",
    description: "Landing page concept with strong visual hierarchy, hover interactions, CTA sections and mobile-first responsive behavior.",
    tags: ["Landing Page", "CSS", "Responsive", "UI Design"]
  },
  {
    title: "Personal Portfolio",
    description: "A portfolio template combining Bootstrap Grid, cards, modal dialogs, carousel components and custom hover effects.",
    tags: ["Portfolio", "Bootstrap 5", "Modal", "Carousel"]
  }
];

const projectModal = document.getElementById("projectModal");

projectModal.addEventListener("show.bs.modal", (event) => {
  const button = event.relatedTarget;
  const index = Number(button.getAttribute("data-project"));
  const project = projects[index];

  document.getElementById("projectModalTitle").textContent = project.title;
  document.getElementById("projectModalHeading").textContent = project.title;
  document.getElementById("projectModalDescription").textContent = project.description;

  const tags = document.getElementById("projectTags");
  tags.innerHTML = project.tags.map(tag =>
    `<span class="badge rounded-pill text-bg-light border px-3 py-2">${tag}</span>`
  ).join("");

  const carousel = bootstrap.Carousel.getOrCreateInstance(
    document.getElementById("projectModalCarousel")
  );
  carousel.to(index % 3);
});

document.getElementById("contactForm").addEventListener("submit", (event) => {
  event.preventDefault();

  const toast = bootstrap.Toast.getOrCreateInstance(
    document.getElementById("successToast")
  );

  toast.show();
  event.target.reset();
});

function downloadCV() {
  const cvText = `ALEX MORGAN
Web Designer, Developer & Creative Problem Solver

Skills:
- Web Design
- Frontend Development
- UI/UX
- Project Management
- Responsive Web Development

Contact:
hello@example.com
`;

  const blob = new Blob([cvText], { type: "text/plain" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = "Alex-Morgan-CV.txt";
  a.click();
  URL.revokeObjectURL(url);
}
