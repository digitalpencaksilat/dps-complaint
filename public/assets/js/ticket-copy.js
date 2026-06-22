(() => {
  const button = document.getElementById("copyTicket");
  const status = document.getElementById("copyTicketStatus");

  if (!button) return;

  button.addEventListener("click", async () => {
    const ticket =
      button.dataset.ticket ||
      document.getElementById("ticketCode")?.textContent.trim() ||
      "";

    try {
      await navigator.clipboard.writeText(ticket);
      status.textContent = "Nomor tiket berhasil disalin.";
      button.innerHTML = '<i class="fas fa-check me-1"></i>Tersalin';

      setTimeout(() => {
        status.textContent = "";
        button.innerHTML = '<i class="fas fa-copy me-1"></i>Copy';
      }, 1800);
    } catch (error) {
      status.textContent =
        "Gagal copy otomatis. Silakan blok nomor tiket lalu copy manual.";
    }
  });
})();
