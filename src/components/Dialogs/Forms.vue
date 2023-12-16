<template>
  <v-dialog
    v-model="products.product.visible"
    :fullscreen="products.product.fullscreen"
    :max-width="!products.product.fullscreen ? 900 : 0"
    :hide-overlay="products.product.fullscreen"
    persistent
  >
    <v-card flat tile>
      <v-toolbar
        height="35"
        dark
        :src="
          $store.getters.server + 'img/backdrops/' + $store.getters.background
        "
      >
        <template v-slot:img="{ props }">
          <v-img
            v-bind="props"
            gradient="to top right, rgba(57,181,74,.9), rgba(25,32,72,.6)"
          />
        </template>
        <v-icon size="18">mdi-webpack</v-icon>
        <span class="body-2 ml-2" style="margin-top: 1px">{{
          products.product.selected
        }}</span>
        <v-icon>mdi-circle-small</v-icon>
        <span class="body-2 yellow--text" style="margin-top: 1px">{{
          products.categories.selected
        }}</span>
        <span v-if="products.categories.CategoryObject.description">
          <v-icon>mdi-circle-small</v-icon>
          <span
            class="body-2 light-green--text text--accent-2"
            style="margin-top: 1px"
            >{{ products.categories.CategoryObject.description }}</span
          >
        </span>
        <v-spacer />
        <v-icon
          size="18"
          @click="
            products.product.fullscreen
              ? (products.product.fullscreen = false)
              : (products.product.fullscreen = true)
          "
        >
          {{
            products.product.fullscreen
              ? "mdi-window-restore"
              : "mdi-window-maximize"
          }}
        </v-icon>
        <v-icon size="18" class="ml-5" @click="products.product.visible = false"
          >mdi-close</v-icon
        >
      </v-toolbar>
      <div
        :style="
          products.product.fullscreen
            ? 'max-height: 90vh; overflow-y: auto'
            : 'max-height: 75vh; overflow-y: auto'
        "
      >
        <ShortTermPaybill
          v-if="products.product.selected == 'Short Term Paybill'"
          :products="products"
        />
        <SendToBusiness
          v-else-if="products.product.selected == 'Send To Business'"
          :products="products"
        />
        <BulkPayment
          v-else-if="products.product.selected == 'Bulk Payment'"
          :products="products"
        />
        <BuyGoodsIndividualWithBankDetails
          v-else-if="
            products.product.selected === 'Buy Goods' &&
            products.categories.selected === 'Individual' &&
            products.categories.CategoryObject.description ===
              'Settles to owner\'s M-PESA and Bank'
          "
          :products="products"
        />
        <BuyGoodsIndividualWithoutBankDetails
          v-else-if="
            products.product.selected === 'Buy Goods' &&
            products.categories.selected === 'Individual' &&
            products.categories.CategoryObject.description ===
              'Settles to owner\'s M-PESA Only'
          "
          :products="products"
        />
        <BuyGoodsSoleProprietorWithBankDetails
          v-else-if="
            products.product.selected === 'Buy Goods' &&
            products.categories.selected === 'Sole Proprietor' &&
            products.categories.CategoryObject.description ===
              'Settles to owner\'s M-PESA and Bank'
          "
          :products="products"
        />
        <BuyGoodsSoleProprietorWithoutBankDetails
          v-else-if="
            products.product.selected === 'Buy Goods' &&
            products.categories.selected === 'Sole Proprietor' &&
            products.categories.CategoryObject.description ===
              'Settles to owner\'s M-PESA Only'
          "
          :products="products"
        />
        <LimitedLiabilityCompany
          v-else-if="
            products.product.selected === 'Buy Goods' &&
            products.categories.selected === 'Limited Liability Company'
          "
          :products="products"
        />
      </div>
    </v-card>
  </v-dialog>
</template>

<script>
import ShortTermPaybill from "@/components/Products/ShortTermPaybill";
import SendToBusiness from "@/components/Products/SendToBusiness";
import BulkPayment from "@/components/Products/BulkPayment";
import BuyGoodsIndividualWithBankDetails from "@/components/Products/BuyGoods/IndividualWithBankDetails";
import BuyGoodsIndividualWithoutBankDetails from "@/components/Products/BuyGoods/IndividualWithoutBankDetails";
import BuyGoodsSoleProprietorWithBankDetails from "@/components/Products/BuyGoods/SoleProprietorWithBankDetails";
import BuyGoodsSoleProprietorWithoutBankDetails from "@/components/Products/BuyGoods/SoleProprietorWithoutBankDetails";
import LimitedLiabilityCompany from "@/components/Products/BuyGoods/LimitedLiabilityCompany";
export default {
  components: {
    ShortTermPaybill,
    SendToBusiness,
    BulkPayment,
    BuyGoodsIndividualWithBankDetails,
    BuyGoodsIndividualWithoutBankDetails,
    BuyGoodsSoleProprietorWithBankDetails,
    BuyGoodsSoleProprietorWithoutBankDetails,
    LimitedLiabilityCompany,
  },
  props: ["products"],
};
</script>
