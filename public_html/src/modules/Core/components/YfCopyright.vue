<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <hook-wrapper class="Core-Component-YfCopyright">
    <div class="flex">
      <div>
        <div>&copy; YetiForce.com All rights reserved.</div>
        <q-separator dark />
        <div class="flex no-wrap justify-between q-py-xs">
          <a class="q-link text-blue-grey-11" href="https://www.linkedin.com/groups/8177576" rel="noreferrer noopener">
            <q-icon size="16px" name="mdi-linkedin" title="Linkedin"></q-icon>
          </a>
          <q-separator class="q-mx-sm" dark vertical />
          <a class="q-link text-blue-grey-11" href="https://twitter.com/YetiForceEN" rel="noreferrer noopener">
            <q-icon size="16px" name="mdi-twitter" title="Twitter"></q-icon>
          </a>
          <q-separator class="q-mx-sm" dark vertical />
          <a
            class="q-link text-blue-grey-11"
            href="https://www.facebook.com/YetiForce-CRM-158646854306054/"
            rel="noreferrer noopener"
          >
            <q-icon size="16px" name="mdi-facebook" title="Facebook"></q-icon>
          </a>
          <q-separator class="q-mx-sm" dark vertical />
          <a
            class="q-link text-blue-grey-11"
            href="https://github.com/YetiForceCompany/YetiForceCRM"
            rel="noreferrer noopener"
          >
            <q-icon size="16px" name="mdi-github-circle" title="Github"></q-icon>
          </a>
          <q-separator class="q-mx-sm" dark vertical />

          <a class="q-link text-blue-grey-11" href="https://yetiforce.shop" rel="noreferrer noopener">
            <q-icon size="18px" name="mdi-cart-outline" title="yetiforce.shop"></q-icon>
          </a>
          <q-separator class="q-mx-sm" dark vertical />
          <a class="q-link text-blue-grey-11" href="#" role="button">
            <q-icon size="18px" name="mdi-information-outline" title="YetiForceCRM"></q-icon>
          </a>
        </div>
      </div>
      <div class="flex flex-center gt-xs q-ml-sm">
        <q-avatar size="32px" class="q-mx-auto">
          <img src="/src/statics/Logo/white_logo_yetiforce.png" alt="yetiforce logo" />
        </q-avatar>
      </div>
    </div>
  </hook-wrapper>
</template>

<script>
export default {
  name: 'YfCopyright'
}
</script>

<style module lang="stylus"></style>
