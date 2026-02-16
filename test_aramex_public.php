<?php
use Illuminate\Support\Facades\Http;

function testAramexPublic()
{
    $waybill = '37301449765';
    $xml = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v1="http://ws.aramex.net/ShippingAPI/v1/">
   <soapenv:Header/>
   <soapenv:Body>
      <v1:ShipmentTrackingRequest>
         <v1:Transaction>
            <v1:Reference1>Public_Track</v1:Reference1>
         </v1:Transaction>
         <v1:Shipments>
            <v1:string>{$waybill}</v1:string>
         </v1:Shipments>
         <v1:GetLastTrackingUpdateOnly>false</v1:GetLastTrackingUpdateOnly>
      </v1:ShipmentTrackingRequest>
   </soapenv:Body>
</soapenv:Envelope>
XML;

    $ch = curl_init('http://ws.aramex.net/shippingapi/tracking/service_1_0.svc');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: text/xml; charset=utf-8',
        'SOAPAction: http://ws.aramex.net/ShippingAPI/v1/Service_1_0/TrackShipments',
    ]);

    $res = curl_exec($ch);
    echo "Response:\n" . $res;
}

testAramexPublic();
?>