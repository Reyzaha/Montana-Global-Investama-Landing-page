import "package:flutter_test/flutter_test.dart";
import "package:mgi_investor/api/endpoint/auth/auth_request.dart";
import "package:mgi_investor/helper/formats.dart";
import "package:mgi_investor/helper/generals.dart";
import "package:mgi_investor/helper/images.dart";
import "package:mgi_investor/model/account.dart";

void main() {
  group("Formats Helper Tests", () {
    test("Formats coalesce and spell numbers", () {
      expect(Formats.coalesce("MGI"), "MGI");
      expect(Formats.coalesce(null, defaultString: "Fallback"), "Fallback");
      expect(Formats.tryParseNumber(500000000), 500000000.0);
      expect(Formats.tryParseNumber("1500000000"), 1500000000.0);
      expect(Formats.spell(1500000000), "1.500.000.000");
    });
  });

  group("Account Model Tests", () {
    test("Individual account displayName and parsing", () {
      final account = Account().parse({
        "id": 1,
        "type": "perorangan",
        "email": "investor@montana.co.id",
        "full_name": "Budi Santoso",
      });

      expect(account.id, 1);
      expect(account.type, AccountType.individual);
      expect(account.displayName(), "Budi Santoso");
    });

    test("Institution account displayName and parsing", () {
      final account = Account().parse({
        "id": 2,
        "type": "perusahaan",
        "email": "investasi@perusahaan.com",
        "business_name": "PT Mitra Bangun Persada",
        "pic_name": "Hendra Wijaya",
      });

      expect(account.id, 2);
      expect(account.type, AccountType.institution);
      expect(account.displayName(), "PT Mitra Bangun Persada");
    });
  });

  group("Auth Request Models Tests", () {
    test("SignInRequest JSON serialization", () {
      final request = SignInRequest(
        email: "investor@montana.co.id",
        password: "secretpassword",
        type: "perorangan",
      );

      final json = request.toJson();
      expect(json["email"], "investor@montana.co.id");
      expect(json["password"], "secretpassword");
      expect(json["type"], "perorangan");
    });

    test("RegisterIndividualRequest JSON serialization", () {
      final request = RegisterIndividualRequest(
        email: "pemodal@gmail.com",
        password: "password123",
        fullName: "Ahmad Dahlan",
        phone: "081234567890",
      );

      final json = request.toJson();
      expect(json["full_name"], "Ahmad Dahlan");
      expect(json["type"], "perorangan");
      expect(json["phone"], "081234567890");
    });
  });

  group("Generals Utilities Tests", () {
    test("Hashing functions", () {
      expect(Generals.md5("mgi_test").isNotEmpty, true);
      expect(Generals.sha1("mgi_test").isNotEmpty, true);
      expect(Generals.sha256("mgi_test").isNotEmpty, true);
    });
  });

  group("Images Helper Tests", () {
    test("App icon and brand logo paths are valid", () {
      expect(Images.brandLogo(), "assets/image/mgi_investor_logo.png");
      expect(Images.appIcon(), "assets/image/app_icon.png");
    });
  });
}
