import { IncomingForm } from "formidable";
import fs from "fs";
import { Resend } from "resend";

export const config = {
  api: {
    bodyParser: false, // 🔴 wichtig für multipart/form-data
  },
};

const resend = new Resend(process.env.RESEND_API_KEY);

export default async function handler(req, res) {
  if (req.method !== "POST") {
    return res.status(405).json({ error: "Method not allowed" });
  }

  const form = new IncomingForm({
    multiples: true,
    keepExtensions: true,
    maxFileSize: 5 * 1024 * 1024, // 5 MB pro Datei
  });

  form.parse(req, async (err, fields, files) => {
    if (err) {
      console.error(err);
      return res.status(400).json({ error: "Formular konnte nicht gelesen werden" });
    }

    try {
      // ---------- Textdaten ----------
      const data = Object.entries(fields)
        .map(([k, v]) => `<strong>${k}</strong>: ${v}`)
        .join("<br>");

      // ---------- Anhänge ----------
      let attachments = [];

      if (files.attachments) {
        const fileArray = Array.isArray(files.attachments)
          ? files.attachments
          : [files.attachments];

        attachments = fileArray.map(f => ({
          filename: f.originalFilename,
          content: fs.readFileSync(f.filepath),
        }));
      }

      // ---------- Mail senden ----------
      await resend.emails.send({
        from: 'onboarding@resend.dev',
        to: 'lindrithasani@gmail.com', // ← deine Ziel-Mail
        subject: "Neue Umzugsanfrage",
        html: `
          <h2>Neue Anfrage</h2>
          <p>${data}</p>
        `,
        attachments,
      });

      return res.status(200).json({ ok: true });
    } catch (e) {
      console.error(e);
      return res.status(500).json({ error: "Mailversand fehlgeschlagen" });
    }
  });
}
