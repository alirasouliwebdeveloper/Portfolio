import Script from "next/script";

/** GA4 loader. The measurement id comes from the admin settings; anything that isn't a G- id is ignored. */
export function GoogleAnalytics({ id }: { id: string | null }) {
  if (!id || !/^G-[A-Z0-9]{4,20}$/.test(id)) return null;

  return (
    <>
      <Script
        src={`https://www.googletagmanager.com/gtag/js?id=${id}`}
        strategy="afterInteractive"
      />
      <Script id="ga-init" strategy="afterInteractive">
        {`window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','${id}',{anonymize_ip:true});`}
      </Script>
    </>
  );
}
